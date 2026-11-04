<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\GoodsReceipt;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoodsReceiptStandaloneControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $gudangUser;
    protected Warehouse $warehouse;
    protected Supplier $supplier;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);

        $this->warehouse = Warehouse::firstOrCreate(['code' => 'W-PRJ-001'], ['name' => 'Gudang Site FK Teknik', 'is_central' => false]);
        $this->supplier = Supplier::firstOrCreate(['code' => 'SUP-TB-01'], ['name' => 'TB Sumber Bangunan']);
        $this->category = Category::firstOrCreate(['code' => 'CAT-ARSITEK'], ['name' => 'ARSITEK', 'type' => 'material']);

        $this->gudangUser = User::factory()->create(['email_verified_at' => now()]);
        $this->gudangUser->assignRole('Admin Gudang Proyek');
        $this->gudangUser->warehouses()->attach($this->warehouse->id);
    }

    public function test_can_store_and_confirm_goods_receipt_directly_without_po(): void
    {
        $response = $this->actingAs($this->gudangUser)->post(route('goods-receipts.store'), [
            'warehouse_id'    => $this->warehouse->id,
            'supplier_id'     => $this->supplier->id,
            'received_at'     => now()->toDateString(),
            'invoice_number'  => 'DO-VENDOR-404',
            'notes'           => 'Penerimaan keramik lantai langsung',
            'items' => [
                [
                    'item_type'     => 'material',
                    'name'          => 'Keramik Granit 60x60 Putih',
                    'category_id'   => $this->category->id,
                    'custom_unit'   => 'dus',
                    'qty_received'  => 50,
                    'condition'     => 'good',
                ]
            ]
        ]);

        $response->assertSessionHasNoErrors();
        $gr = GoodsReceipt::latest('id')->first();
        $this->assertNotNull($gr);
        $this->assertEquals('DO-VENDOR-404', $gr->invoice_number);
        $this->assertNull($gr->purchase_order_id);
    }

    public function test_goods_receipt_create_view_renders_direct_item_inputs_without_mandatory_po_select(): void
    {
        $response = $this->actingAs($this->gudangUser)->get(route('goods-receipts.create'));
        $response->assertOk();
        $response->assertSee('Penerimaan Barang Langsung');
        $response->assertSee('Tambah Baris Barang');
        $response->assertDontSee('Pilih Purchase Order (Wajib)');
        $response->assertDontSee('Hubungkan PO Lapangan');
        $response->assertDontSee('Hubungkan Purchase Order (PO)');
        $response->assertDontSee('name="purchase_order_id"', false);
        $response->assertDontSee('name="material_request_id"', false);
        $response->assertDontSee('Qty Target (PO)');
        $response->assertSee('Qty Nota / SJ');
        $response->assertSee('Selisih/Hilang');
        $response->assertSee('Diterima (Stok)');
        $response->assertSee('Kode Material');
    }

    public function test_goods_receipt_create_only_references_items_from_users_own_warehouse(): void
    {
        $unit = \App\Models\Unit::firstOrCreate(['code' => 'ZAK'], ['name' => 'Zak']);
        $otherWarehouse = Warehouse::firstOrCreate(['code' => 'W-OTHER-002'], ['name' => 'Gudang Proyek Lain', 'is_central' => false]);

        $materialOwn = \App\Models\Material::create([
            'sku' => 'MAT-OWN-01',
            'name' => 'Semen Padang Gudang Sendiri',
            'category_id' => $this->category->id,
            'unit_id' => $unit->id,
        ]);
        \App\Models\Inventory::create([
            'material_id' => $materialOwn->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 10,
        ]);

        $materialOther = \App\Models\Material::create([
            'sku' => 'MAT-OTHER-02',
            'name' => 'Besi Ulir Gudang Orang Lain',
            'category_id' => $this->category->id,
            'unit_id' => $unit->id,
        ]);
        \App\Models\Inventory::create([
            'material_id' => $materialOther->id,
            'warehouse_id' => $otherWarehouse->id,
            'quantity' => 20,
        ]);

        $response = $this->actingAs($this->gudangUser)->get(route('goods-receipts.create'));
        $response->assertOk();

        $responseMaterials = $response->viewData('materials');
        $this->assertTrue($responseMaterials->contains('id', $materialOwn->id));
        $this->assertFalse($responseMaterials->contains('id', $materialOther->id));
    }

    public function test_goods_receipt_edit_view_renders_direct_item_inputs_without_po_select(): void
    {
        $gr = GoodsReceipt::create([
            'receipt_number' => 'GR-TEST-001',
            'warehouse_id' => $this->warehouse->id,
            'supplier_id' => $this->supplier->id,
            'receipt_date' => now()->toDateString(),
            'status' => 'draft',
            'received_by_user_id' => $this->gudangUser->id,
        ]);

        $response = $this->actingAs($this->gudangUser)->get(route('goods-receipts.edit', $gr));
        $response->assertOk();
        $response->assertSee('Tambah Baris Barang');
        $response->assertDontSee('Hubungkan PO Lapangan');
        $response->assertDontSee('Hubungkan Purchase Order (PO)');
        $response->assertDontSee('name="purchase_order_id"', false);
        $response->assertDontSee('name="material_request_id"', false);
        $response->assertDontSee('Qty Target (PO)');
        $response->assertSee('Qty Nota / SJ');
        $response->assertSee('Selisih/Hilang');
        $response->assertSee('Diterima (Stok)');
        $response->assertSee('Kategori');
        $response->assertSee('Satuan');
        $response->assertSee('Kode Material');
    }

    public function test_can_update_draft_goods_receipt_with_direct_material_inputs(): void
    {
        $gr = GoodsReceipt::create([
            'receipt_number' => 'GR-TEST-UPDATE-01',
            'warehouse_id' => $this->warehouse->id,
            'supplier_id' => $this->supplier->id,
            'receipt_date' => now()->toDateString(),
            'status' => 'draft',
            'received_by_user_id' => $this->gudangUser->id,
        ]);

        $response = $this->actingAs($this->gudangUser)->put(route('goods-receipts.update', $gr), [
            'warehouse_id'    => $this->warehouse->id,
            'supplier_id'     => $this->supplier->id,
            'received_at'     => now()->toDateString(),
            'invoice_number'  => 'INV-UPDATED-99',
            'notes'           => 'Update draft catatan',
            'items' => [
                [
                    'item_type'     => 'material',
                    'name'          => 'Bata Merah Press Super',
                    'category_id'   => $this->category->id,
                    'custom_unit'   => 'pcs',
                    'quantity'      => 1000,
                    'condition'     => 'good',
                ]
            ]
        ]);

        $response->assertSessionHasNoErrors();
        $gr->refresh();
        $this->assertEquals('INV-UPDATED-99', $gr->invoice_number);
        $this->assertCount(1, $gr->items);
        $this->assertEquals('Bata Merah Press Super', $gr->items->first()->item_name);
        $this->assertEquals(1000, (float) $gr->items->first()->qty_received);
    }

    public function test_direct_goods_receipt_with_discrepancy_saves_missing_qty_and_records_in_dynamic_stages(): void
    {
        $response = $this->actingAs($this->gudangUser)->post(route('goods-receipts.store'), [
            'warehouse_id'    => $this->warehouse->id,
            'supplier_id'     => $this->supplier->id,
            'received_at'     => now()->toDateString(),
            'invoice_number'  => 'DO-VENDOR-SELISIH-01',
            'notes'           => 'Barang ada selisih di jalan',
            'items' => [
                [
                    'item_type'     => 'material',
                    'name'          => 'Semen Gresik 50kg Selisih',
                    'category_id'   => $this->category->id,
                    'custom_unit'   => 'sak',
                    'qty_shipped'   => 100,
                    'quantity'      => 95,
                    'qty_missing'   => 5,
                    'missing_action'=> 'lost',
                    'condition'     => 'good',
                ]
            ]
        ]);

        $response->assertSessionHasNoErrors();
        $gr = GoodsReceipt::latest('id')->first();
        $this->assertNotNull($gr);
        $item = $gr->items->first();
        $this->assertEquals(100, (float) $item->qty_shipped);
        $this->assertEquals(95, (float) $item->qty_received);
        $this->assertEquals(5, (float) $item->qty_missing);

        // Konfirmasi Goods Receipt
        $service = app(\App\Services\GoodsReceiptService::class);
        $service->confirm($gr, $this->gudangUser->id);

        $item->refresh();
        $this->assertEquals('confirmed_lost', $item->missing_status);

        // Verifikasi material terdaftar dan tahapan masuk mencatat selisih hilang
        $material = \App\Models\Material::where('name', 'Semen Gresik 50kg Selisih')->first();
        $this->assertNotNull($material);

        $stages = $material->getDynamicIncomingStages();
        $this->assertNotEmpty($stages);
        $stage = $stages->first();
        $this->assertEquals(100, $stage['planned_qty']);
        $this->assertEquals(95, $stage['received_qty']);
        $this->assertEquals(5, $stage['lost_qty']);
        $this->assertEquals('partial', $stage['status']);
        $this->assertStringContainsString('Masuk Ada Selisih', $stage['status_label']);
    }
}

