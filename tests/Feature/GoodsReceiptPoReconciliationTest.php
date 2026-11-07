<?php

namespace Tests\Feature;

use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\Material;
use App\Models\MaterialRequest;
use App\Models\MaterialRequestItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use Database\Seeders\MasterDataSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GoodsReceiptPoReconciliationTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminGudang;
    protected User $adminPengadaan;
    protected Warehouse $warehouse;
    protected Supplier $supplier;
    protected Material $materialA;
    protected Material $materialB;
    protected MaterialRequest $mr;
    protected PurchaseOrder $po;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(MasterDataSeeder::class);

        $this->warehouse = Warehouse::firstOrCreate(
            ['code' => 'W-RECON-01'],
            ['name' => 'Gudang Proyek Recon', 'is_central' => false]
        );

        $this->supplier = Supplier::firstOrCreate(
            ['name' => 'Supplier Mitra Reconcile'],
            ['phone' => '081234567888']
        );

        $category = \App\Models\Category::firstOrCreate(['name' => 'Struktur'], ['code' => 'CAT-STR', 'type' => 'material']);
        $unit = \App\Models\Unit::firstOrCreate(['code' => 'sak'], ['name' => 'Sak']);

        $this->materialA = Material::firstOrCreate(
            ['sku' => 'MAT-RECON-A'],
            ['name' => 'Semen Tiga Roda 50kg', 'category_id' => $category->id, 'unit_id' => $unit->id]
        );
        $this->materialB = Material::firstOrCreate(
            ['sku' => 'MAT-RECON-B'],
            ['name' => 'Besi Beton 12mm Polos', 'category_id' => $category->id, 'unit_id' => $unit->id]
        );

        $this->adminGudang = User::factory()->create(['name' => 'Petugas Gudang Proyek']);
        $this->adminGudang->assignRole('Admin Gudang Proyek');
        $this->adminGudang->warehouses()->attach($this->warehouse->id);

        $this->adminPengadaan = User::factory()->create(['name' => 'Tim Pengadaan Pusat']);
        $this->adminPengadaan->assignRole('Admin PO');
        $this->adminPengadaan->warehouses()->attach($this->warehouse->id);

        // 1. Buat PO Lapangan (Material Request)
        $this->mr = MaterialRequest::create([
            'request_number' => 'MR-RECON-001',
            'to_warehouse_id' => $this->warehouse->id,
            'status' => 'approved',
            'requested_by_user_id' => $this->adminGudang->id,
            'approved_by_user_id' => $this->adminPengadaan->id,
            'approved_at' => now(),
            'notes' => 'PO Lapangan Test',
        ]);

        $mrItemA = MaterialRequestItem::create([
            'material_request_id' => $this->mr->id,
            'material_id' => $this->materialA->id,
            'qty_requested' => 100,
            'qty_approved' => 100,
            'qty_ordered' => 100,
            'qty_received' => 0,
        ]);

        $mrItemB = MaterialRequestItem::create([
            'material_request_id' => $this->mr->id,
            'material_id' => $this->materialB->id,
            'qty_requested' => 50,
            'qty_approved' => 50,
            'qty_ordered' => 50,
            'qty_received' => 0,
        ]);

        // 2. Buat Purchase Order (PO Vendor)
        $this->po = PurchaseOrder::create([
            'po_number' => 'PO-RECON-2026-001',
            'supplier_id' => $this->supplier->id,
            'material_request_id' => $this->mr->id,
            'delivery_warehouse_id' => $this->warehouse->id,
            'status' => 'sent',
            'created_by' => $this->adminPengadaan->id,
            'total_amount' => 15000000,
            'order_date' => now()->toDateString(),
        ]);

        PurchaseOrderItem::create([
            'purchase_order_id' => $this->po->id,
            'material_id' => $this->materialA->id,
            'material_request_item_id' => $mrItemA->id,
            'quantity' => 100,
            'received_qty' => 0,
            'unit_price' => 75000,
            'subtotal' => 7500000,
        ]);

        PurchaseOrderItem::create([
            'purchase_order_id' => $this->po->id,
            'material_id' => $this->materialB->id,
            'material_request_item_id' => $mrItemB->id,
            'quantity' => 50,
            'received_qty' => 0,
            'unit_price' => 150000,
            'subtotal' => 7500000,
        ]);
    }

    public function test_goods_receipt_with_linked_mr_and_po_and_attachment_reconciles_quantities_and_displays_in_po_views(): void
    {
        Storage::fake('public');

        $poItemA = $this->po->items->where('material_id', $this->materialA->id)->first();
        $mrItemA = $this->mr->items->where('material_id', $this->materialA->id)->first();

        $file = UploadedFile::fake()->image('surat_jalan_vendor.jpg', 600, 800);

        // 1. Simpan Penerimaan Barang dari Lapangan terhubung PO Lapangan & PO Vendor
        $response = $this->actingAs($this->adminGudang)->post(route('goods-receipts.store'), [
            'warehouse_id' => $this->warehouse->id,
            'supplier_id' => $this->supplier->id,
            'material_request_id' => $this->mr->id,
            'purchase_order_id' => $this->po->id,
            'invoice_number' => 'SJ-VENDOR-99881',
            'received_at' => now()->toDateString(),
            'notes' => 'Penerimaan bertahap tahap 1',
            'attachment' => $file,
            'items' => [
                [
                    'item_type' => 'material',
                    'material_id' => $this->materialA->id,
                    'purchase_order_item_id' => $poItemA->id,
                    'material_request_item_id' => $mrItemA->id,
                    'name' => $this->materialA->name,
                    'custom_unit' => 'sak',
                    'quantity' => 60, // Terima 60 dari 100
                    'condition' => 'good',
                ]
            ]
        ]);

        $response->assertSessionHasNoErrors();
        $gr = GoodsReceipt::latest('id')->first();
        $this->assertNotNull($gr);
        $this->assertEquals($this->po->id, $gr->purchase_order_id);
        $this->assertEquals($this->mr->id, $gr->material_request_id);
        $this->assertEquals('SJ-VENDOR-99881', $gr->invoice_number);
        $this->assertNotNull($gr->attachment_path);
        Storage::disk('public')->assertExists($gr->attachment_path);

        // 2. Konfirmasi Penerimaan Barang
        $confirmResponse = $this->actingAs($this->adminGudang)->post(route('goods-receipts.confirm', $gr));
        $confirmResponse->assertSessionHasNoErrors();

        // Verifikasi received_qty di PurchaseOrderItem dan MaterialRequestItem ter-update
        $poItemA->refresh();
        $mrItemA->refresh();
        $this->assertEquals(60, (float) $poItemA->received_qty);
        $this->assertEquals(60, (float) $mrItemA->qty_received);

        // 3. Verifikasi Halaman Detail PO (Pengadaan View)
        $poShowResponse = $this->actingAs($this->adminPengadaan)->get(route('purchase-orders.show', $this->po));
        $poShowResponse->assertOk();

        // Tabel Rekonsiliasi Fisik
        $poShowResponse->assertSee('Kesesuaian Fisik (Diterima)');
        $poShowResponse->assertSee('Sebagian (60/100)');
        $poShowResponse->assertSee('Sisa: 40');
        $poShowResponse->assertSee('Sebagian');

        // Tabel Verifikasi Penerimaan Barang Fisik Lapangan
        $poShowResponse->assertSee('Verifikasi Penerimaan Barang Fisik Lapangan (Goods Receipts / GR)');
        $poShowResponse->assertSee($gr->receipt_number);
        $poShowResponse->assertSee('SJ-VENDOR-99881');
        $poShowResponse->assertSee('Lihat Foto');

        // 4. Verifikasi Halaman Index PO (Pengadaan List)
        $poIndexResponse = $this->actingAs($this->adminPengadaan)->get(route('purchase-orders.index'));
        $poIndexResponse->assertOk();
        $poIndexResponse->assertSee('Fisik Sebagian');
    }

    public function test_full_goods_receipt_shows_fisik_lengkap_badge(): void
    {
        // Penerimaan penuh untuk semua item
        $poItemA = $this->po->items->where('material_id', $this->materialA->id)->first();
        $poItemB = $this->po->items->where('material_id', $this->materialB->id)->first();
        $mrItemA = $this->mr->items->where('material_id', $this->materialA->id)->first();
        $mrItemB = $this->mr->items->where('material_id', $this->materialB->id)->first();

        $gr = GoodsReceipt::create([
            'receipt_number' => 'GR-FULL-001',
            'warehouse_id' => $this->warehouse->id,
            'supplier_id' => $this->supplier->id,
            'material_request_id' => $this->mr->id,
            'purchase_order_id' => $this->po->id,
            'invoice_number' => 'SJ-FULL-01',
            'receipt_date' => now()->toDateString(),
            'status' => 'draft',
            'received_by_user_id' => $this->adminGudang->id,
        ]);

        GoodsReceiptItem::create([
            'goods_receipt_id' => $gr->id,
            'material_id' => $this->materialA->id,
            'purchase_order_item_id' => $poItemA->id,
            'material_request_item_id' => $mrItemA->id,
            'item_type' => 'material',
            'qty_received' => 100,
            'condition' => 'good',
        ]);

        GoodsReceiptItem::create([
            'goods_receipt_id' => $gr->id,
            'material_id' => $this->materialB->id,
            'purchase_order_item_id' => $poItemB->id,
            'material_request_item_id' => $mrItemB->id,
            'item_type' => 'material',
            'qty_received' => 50,
            'condition' => 'good',
        ]);

        $service = app(\App\Services\GoodsReceiptService::class);
        $service->confirm($gr, $this->adminGudang->id);

        // Verifikasi Index PO
        $poIndexResponse = $this->actingAs($this->adminPengadaan)->get(route('purchase-orders.index'));
        $poIndexResponse->assertOk();
        $poIndexResponse->assertSee('Fisik Lengkap');

        // Verifikasi Show PO
        $poShowResponse = $this->actingAs($this->adminPengadaan)->get(route('purchase-orders.show', $this->po));
        $poShowResponse->assertOk();
        $poShowResponse->assertSee('Sesuai Penuh (100)');
    }
}
