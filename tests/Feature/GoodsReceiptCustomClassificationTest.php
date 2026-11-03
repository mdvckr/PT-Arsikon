<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\Material;
use App\Models\MaterialRequest;
use App\Models\MaterialRequestItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\GoodsReceiptService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GoodsReceiptCustomClassificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Warehouse $projectWarehouse;
    protected Warehouse $centralWarehouse;
    protected Supplier $supplier;
    protected Category $materialCategory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $this->admin = User::where('email', 'admin.pusat@arsikon.co.id')->firstOrFail();
        $this->centralWarehouse = Warehouse::where('is_central', true)->firstOrFail();
        $this->projectWarehouse = Warehouse::where('is_central', false)->firstOrFail();
        $this->supplier = Supplier::firstOrFail();
        $this->materialCategory = Category::where('type', 'material')->firstOrFail();
    }

    public function test_custom_item_without_category_in_mr_can_be_classified_with_custom_sku_in_goods_receipt(): void
    {
        // 1. PO Lapangan (MR) dibuat tanpa kategori & kelompok untuk item custom
        $mr = MaterialRequest::create([
            'request_number'        => 'MR-20261101-0001',
            'requested_by_user_id'  => $this->admin->id,
            'from_warehouse_id'     => $this->centralWarehouse->id,
            'to_warehouse_id'       => $this->projectWarehouse->id,
            'status'                => 'approved',
            'needed_at'             => now()->addDays(2),
        ]);

        $mrItem = MaterialRequestItem::create([
            'material_request_id' => $mr->id,
            'material_id'         => null,
            'item_type'           => 'material',
            'custom_item_name'    => 'Pasir Pasang Extra',
            'custom_item_unit'    => 'rit',
            'custom_item_type'    => null, // Staff lapangan tidak wajib mengisi
            'category_id'         => null, // Staff lapangan tidak wajib mengisi
            'qty_requested'       => 5,
            'qty_approved'        => 5,
            'fulfillment'         => 'purchase',
        ]);

        // 2. PO dibuat dari MR
        $po = PurchaseOrder::create([
            'po_number'             => 'PO-2026-9999',
            'supplier_id'           => $this->supplier->id,
            'material_request_id'   => $mr->id,
            'delivery_warehouse_id' => $this->projectWarehouse->id,
            'created_by'            => $this->admin->id,
            'status'                => 'sent',
            'order_date'            => now(),
            'expected_delivery'     => now()->addDays(1),
        ]);

        $poItem = PurchaseOrderItem::create([
            'purchase_order_id'       => $po->id,
            'material_request_item_id'=> $mrItem->id,
            'material_id'             => null,
            'custom_item_name'        => 'Pasir Pasang Extra',
            'custom_item_unit'        => 'rit',
            'quantity'                => 5,
            'unit_price'              => 850000,
            'subtotal'                => 4250000,
        ]);

        // 3. Saat penerimaan barang (Goods Receipt), admin mengisi Kode SKU, Kategori, dan Kelompok
        $payload = [
            'warehouse_id'     => $this->projectWarehouse->id,
            'supplier_id'      => $this->supplier->id,
            'purchase_order_id'=> $po->id,
            'received_at'      => now()->format('Y-m-d'),
            'received_by_name' => 'Siti Logistik',
            'notes'            => 'Penerimaan pasir proyek tahap 1',
            'items'            => [
                [
                    'item_type'                => 'material',
                    'material_id'              => null,
                    'purchase_order_item_id'   => $poItem->id,
                    'material_request_item_id' => $mrItem->id,
                    'custom_item_name'         => 'Pasir Pasang Extra',
                    'custom_item_unit'         => 'rit',
                    'custom_sku'               => 'PSR-EXTRA-01',
                    'category_id'              => $this->materialCategory->id,
                    'custom_type'              => 'Agregat Halus',
                    'quantity'                 => 5,
                    'qty_shipped'              => 5,
                    'qty_missing'              => 0,
                    'qty_damaged'              => 0,
                    'condition'                => 'good',
                ],
            ],
        ];

        $response = $this->actingAs($this->admin)->post(route('goods-receipts.store'), $payload);
        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $receipt = GoodsReceipt::latest('id')->first();
        $this->assertNotNull($receipt);

        $receiptItem = $receipt->items()->first();
        $this->assertNotNull($receiptItem);
        $this->assertEquals('PSR-EXTRA-01', $receiptItem->custom_sku);
        $this->assertEquals($this->materialCategory->id, $receiptItem->category_id);
        $this->assertEquals('Agregat Halus', $receiptItem->custom_type);

        // 4. Konfirmasi Goods Receipt
        $service = app(GoodsReceiptService::class);
        $confirmedReceipt = $service->confirm($receipt, $this->admin->id);

        $this->assertEquals('confirmed', $confirmedReceipt->status);

        // 5. Cek apakah master material otomatis didaftarkan sesuai SKU, Kategori, dan Kelompok
        $registeredMaterial = Material::where('sku', 'PSR-EXTRA-01')->first();
        $this->assertNotNull($registeredMaterial, 'Material baru harus terdaftar dengan SKU custom dari Goods Receipt.');
        $this->assertEquals('Pasir Pasang Extra', $registeredMaterial->name);
        $this->assertEquals($this->materialCategory->id, $registeredMaterial->category_id);
        $this->assertEquals('Agregat Halus', $registeredMaterial->type);

        // 6. Cek stok inventory di gudang proyek
        $this->assertDatabaseHas('inventories', [
            'warehouse_id' => $this->projectWarehouse->id,
            'material_id'  => $registeredMaterial->id,
            'quantity'     => 5,
        ]);

        // 7. Cek PO item status dan received_qty
        $poItem->refresh();
        $this->assertEquals(5, (float) $poItem->received_qty);
        $po->refresh();
        $this->assertEquals('received', $po->status);
    }
}
