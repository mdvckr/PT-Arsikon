<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\GoodsReceipt;
use App\Models\Material;
use App\Models\MaterialRequest;
use App\Models\MaterialRequestItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PoLapanganCustomItemGroupTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminProyek;
    protected User $adminPusat;
    protected Warehouse $projectWarehouse;
    protected Warehouse $centralWarehouse;
    protected Category $category;
    protected Supplier $supplier;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $this->adminProyek = User::where('email', 'user.proyek@arsikon.co.id')->firstOrFail();
        $this->adminPusat = User::where('email', 'admin.pusat@arsikon.co.id')->firstOrFail();
        $this->projectWarehouse = Warehouse::where('is_central', false)->firstOrFail();
        $this->centralWarehouse = Warehouse::where('is_central', true)->firstOrFail();
        $this->supplier = Supplier::firstOrFail();

        // Siapkan Kategori Material dan satu material dengan kelompok 'Eksterior'
        $this->category = Category::where('type', 'material')->firstOrFail();
        Material::create([
            'category_id' => $this->category->id,
            'unit_id'     => \App\Models\Unit::first()->id,
            'name'        => 'Cat Tembok Standar',
            'sku'         => 'MAT-TEST-CAT-01',
            'type'        => 'Eksterior',
            'is_active'   => true,
        ]);
    }

    public function test_can_create_po_lapangan_with_custom_item_group(): void
    {
        $this->actingAs($this->adminProyek);

        $response = $this->post(route('material-requests.store'), [
            'warehouse_id'        => $this->projectWarehouse->id,
            'source_warehouse_id' => $this->centralWarehouse->id,
            'needed_at'           => now()->addDays(5)->toDateString(),
            'requested_as'        => 'Admin Proyek',
            'notes'               => 'Uji coba input kelompok kustom',
            'custom_items'        => [
                [
                    'name'        => 'Cat Dulux WeatherShield Putih',
                    'category_id' => $this->category->id,
                    'type'        => 'Interior Premium', // Input kelompok kustom oleh user
                    'unit'        => 'Kaleng',
                    'qty'         => 10,
                    'spec'        => '20 Liter',
                    'location'    => 'Lantai 1',
                    'notes'       => 'Warna khusus',
                    'date'        => now()->addDays(5)->toDateString(),
                ],
            ],
        ]);

        $response->assertRedirect();
        $mr = MaterialRequest::latest()->firstOrFail();
        $item = $mr->items->firstOrFail();

        $this->assertEquals('Cat Dulux WeatherShield Putih', $item->custom_item_name);
        $this->assertEquals($this->category->id, $item->category_id);
        $this->assertEquals('Interior Premium', $item->custom_item_type);
        $this->assertEquals('Kaleng', $item->custom_item_unit);
        $this->assertEquals(10, (float) $item->qty_requested);
    }

    public function test_can_update_po_lapangan_custom_item_group(): void
    {
        $this->actingAs($this->adminProyek);

        // Buat MR awal
        $mr = MaterialRequest::create([
            'request_number'       => MaterialRequest::generateRequestNumber(),
            'from_warehouse_id'    => $this->centralWarehouse->id,
            'to_warehouse_id'      => $this->projectWarehouse->id,
            'requested_by_user_id' => $this->adminProyek->id,
            'status'               => 'submitted',
            'needed_at'            => now()->addDays(5)->toDateString(),
            'requested_as'         => 'Admin Proyek',
        ]);

        $item = MaterialRequestItem::create([
            'material_request_id' => $mr->id,
            'category_id'         => $this->category->id,
            'custom_item_name'    => 'Semen Mortar Khusus',
            'custom_item_type'    => 'Plesteran',
            'custom_item_unit'    => 'Zak',
            'qty_requested'       => 20,
            'qty_approved'        => 20,
        ]);

        // Update kelompoknya
        $response = $this->put(route('material-requests.update', $mr), [
            'warehouse_id'        => $this->projectWarehouse->id,
            'source_warehouse_id' => $this->centralWarehouse->id,
            'needed_at'           => now()->addDays(5)->toDateString(),
            'requested_as'        => 'Admin Proyek',
            'custom_items'        => [
                [
                    'name'        => 'Semen Mortar Khusus',
                    'category_id' => $this->category->id,
                    'type'        => 'Acian Instan', // Berubah ke kelompok baru
                    'unit'        => 'Zak',
                    'qty'         => 25,
                ],
            ],
        ]);

        $response->assertRedirect();
        $item->refresh();
        $this->assertEquals('Acian Instan', $item->custom_item_type);
        $this->assertEquals(25, (float) $item->qty_requested);
    }

    public function test_goods_receipt_auto_registers_material_with_specified_group_not_default(): void
    {
        $this->actingAs($this->adminProyek);

        // 1. Buat MR dengan barang manual dan kelompok 'Interior Mewah'
        $mr = MaterialRequest::create([
            'request_number'       => MaterialRequest::generateRequestNumber(),
            'from_warehouse_id'    => null,
            'to_warehouse_id'      => $this->projectWarehouse->id,
            'requested_by_user_id' => $this->adminProyek->id,
            'status'               => 'approved',
            'needed_at'            => now()->addDays(5)->toDateString(),
            'requested_as'         => 'Admin Proyek',
        ]);

        $mrItem = MaterialRequestItem::create([
            'material_request_id' => $mr->id,
            'category_id'         => $this->category->id,
            'custom_item_name'    => 'Lantai Marmer Travertine',
            'custom_item_type'    => 'Interior Mewah', // Kelompok yang dipilih user
            'custom_item_unit'    => 'm2',
            'qty_requested'       => 100,
            'qty_approved'        => 100,
            'specification'       => 'Ukuran 60x120 polish',
        ]);

        // 2. Buat PO yang mengaitkan MR item ini
        $po = PurchaseOrder::create([
            'po_number'           => 'PO-2026-TEST-MARMER',
            'material_request_id' => $mr->id,
            'supplier_id'         => $this->supplier->id,
            'supplier_name'       => $this->supplier->name,
            'created_by'          => $this->adminPusat->id,
            'status'              => 'sent',
            'order_date'          => now()->toDateString(),
            'total_amount'        => 15000000,
        ]);

        $poItem = PurchaseOrderItem::create([
            'purchase_order_id'        => $po->id,
            'material_request_item_id' => $mrItem->id,
            'material_id'              => null,
            'custom_item_name'         => 'Lantai Marmer Travertine',
            'custom_item_type'         => 'Interior Mewah',
            'custom_item_unit'         => 'm2',
            'quantity'                 => 100,
            'received_qty'             => 0,
            'unit_price'               => 150000,
            'total_price'              => 15000000,
        ]);

        // 3. Terima barang di GoodsReceiptController::store
        $response = $this->post(route('goods-receipts.store'), [
            'purchase_order_id' => $po->id,
            'supplier_id'       => $this->supplier->id,
            'warehouse_id'      => $this->projectWarehouse->id,
            'received_at'       => now()->toDateString(),
            'invoice_number'    => 'SJ-MARMER-01',
            'items'             => [
                [
                    'material_id'            => null,
                    'purchase_order_item_id' => $poItem->id,
                    'quantity'               => 100,
                    'unit_price'             => 150000,
                ],
            ],
        ]);

        $response->assertSessionHas('success');

        // Pastikan material terdaftar ke master dengan type 'Interior Mewah', BUKAN 'Eksterior'!
        $registeredMaterial = Material::where('name', 'Lantai Marmer Travertine')->first();
        $this->assertNotNull($registeredMaterial, 'Material harus otomatis terdaftar di master materials.');
        $this->assertEquals($this->category->id, $registeredMaterial->category_id);
        $this->assertEquals('Interior Mewah', $registeredMaterial->type, 'Kelompok material harus sesuai input custom user, bukan default Eksterior.');
        $this->assertEquals('Ukuran 60x120 polish', $registeredMaterial->size);
    }
}
