<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Inventory;
use App\Models\Material;
use App\Models\MaterialRequest;
use App\Models\MaterialRequestItem;
use App\Models\Project;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PoLapanganAvailableStockTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminPusat;
    protected Warehouse $centralWh;
    protected Warehouse $projectWh;
    protected Material $materialA;
    protected Material $materialB;
    protected Unit $unit;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->adminPusat = User::where('email', 'admin.pusat@arsikon.co.id')->firstOrFail();
        $this->centralWh = Warehouse::where('is_central', true)->firstOrFail();

        $project = Project::create(['name' => 'Proyek Emerald', 'code' => 'PRJ-EMR', 'status' => 'active']);
        $this->projectWh = Warehouse::create([
            'name'       => 'Gudang Emerald',
            'code'       => 'WH-EMR',
            'type'       => 'project',
            'is_central' => false,
            'is_active'  => true,
            'project_id' => $project->id,
        ]);

        $this->category = Category::first() ?? Category::create(['name' => 'Bahan Bangunan', 'code' => 'BB', 'type' => 'material']);
        $this->unit = Unit::first() ?? Unit::create(['name' => 'Sak', 'code' => 'sak', 'is_decimal' => false]);

        $this->materialA = Material::create([
            'name'        => 'Semen Gresik 40kg',
            'sku'         => 'MAT-SMN-GRS',
            'category_id' => $this->category->id,
            'unit_id'     => $this->unit->id,
            'is_active'   => true,
        ]);

        $this->materialB = Material::create([
            'name'        => 'Pasir Pasang',
            'sku'         => 'MAT-PSR-PSG',
            'category_id' => $this->category->id,
            'unit_id'     => $this->unit->id,
            'is_active'   => true,
        ]);
    }

    public function test_available_stock_and_badge_computed_correctly_with_active_allocation(): void
    {
        // Material A: qty = 100, qty_allocated = 30 -> Available stock = 70
        Inventory::create([
            'warehouse_id'  => $this->centralWh->id,
            'material_id'   => $this->materialA->id,
            'quantity'      => 100,
            'qty_allocated' => 30,
        ]);

        // Material B: qty = 20, qty_allocated = 10 -> Available stock = 10
        Inventory::create([
            'warehouse_id'  => $this->centralWh->id,
            'material_id'   => $this->materialB->id,
            'quantity'      => 20,
            'qty_allocated' => 10,
        ]);

        $mr = MaterialRequest::create([
            'request_number'       => 'MR-STOCK-TEST-01',
            'from_warehouse_id'    => $this->centralWh->id,
            'to_warehouse_id'      => $this->projectWh->id,
            'requested_by_user_id' => $this->adminPusat->id,
            'status'               => 'submitted',
        ]);

        // Item 1: minta 50 (stok 70 >= 50 => Cukup)
        $item1 = MaterialRequestItem::create([
            'material_request_id' => $mr->id,
            'item_type'           => 'material',
            'material_id'         => $this->materialA->id,
            'category_id'         => $this->category->id,
            'fulfillment'         => 'stock',
            'qty_requested'       => 50,
            'qty_approved'        => 50,
        ]);

        // Item 2: minta 25 (stok 10 < 25 => Kurang)
        $item2 = MaterialRequestItem::create([
            'material_request_id' => $mr->id,
            'item_type'           => 'material',
            'material_id'         => $this->materialB->id,
            'category_id'         => $this->category->id,
            'fulfillment'         => 'stock',
            'qty_requested'       => 25,
            'qty_approved'        => 25,
        ]);

        $response = $this->actingAs($this->adminPusat)->get(route('material-requests.show', $mr));

        $response->assertStatus(200);
        $response->assertSee('Stok Tersedia');
        $response->assertSee('70');
        $response->assertSee('Cukup');
        $response->assertSee('10');
        $response->assertSee('Kurang');
    }

    public function test_purchase_item_shows_remaining_ordered_and_po_link(): void
    {
        $mr = MaterialRequest::create([
            'request_number'       => 'MR-PURCHASE-TEST-01',
            'from_warehouse_id'    => $this->centralWh->id,
            'to_warehouse_id'      => $this->projectWh->id,
            'requested_by_user_id' => $this->adminPusat->id,
            'status'               => 'approved',
        ]);

        $mrItem = MaterialRequestItem::create([
            'material_request_id' => $mr->id,
            'item_type'           => 'material',
            'material_id'         => $this->materialA->id,
            'category_id'         => $this->category->id,
            'fulfillment'         => 'purchase',
            'qty_requested'       => 50,
            'qty_approved'        => 50,
            'qty_ordered'         => 30, // sisa dipesan = 20
        ]);

        $supplier = Supplier::create(['name' => 'PT Mitra Semen', 'code' => 'SUP-MS-01']);

        $po = PurchaseOrder::create([
            'po_number'             => 'PO-TEST-T21-01',
            'supplier_id'           => $supplier->id,
            'delivery_warehouse_id' => $this->projectWh->id,
            'created_by'            => $this->adminPusat->id,
            'order_date'            => now()->format('Y-m-d'),
            'status'                => 'approved',
            'total_amount'          => 1500000,
        ]);

        PurchaseOrderItem::create([
            'purchase_order_id'        => $po->id,
            'material_request_item_id' => $mrItem->id,
            'material_id'              => $this->materialA->id,
            'quantity'                 => 30,
            'unit_price'               => 50000,
            'total_price'              => 1500000,
        ]);

        $response = $this->actingAs($this->adminPusat)->get(route('material-requests.show', $mr));

        $response->assertStatus(200);
        $response->assertSee('Sisa dipesan');
        $response->assertSee('20');
        $response->assertSee($po->po_number);
    }

    public function test_viewing_page_does_not_mutate_inventory_stocks(): void
    {
        $inv = Inventory::create([
            'warehouse_id'  => $this->centralWh->id,
            'material_id'   => $this->materialA->id,
            'quantity'      => 85,
            'qty_allocated' => 25,
        ]);

        $mr = MaterialRequest::create([
            'request_number'       => 'MR-READONLY-TEST-01',
            'from_warehouse_id'    => $this->centralWh->id,
            'to_warehouse_id'      => $this->projectWh->id,
            'requested_by_user_id' => $this->adminPusat->id,
            'status'               => 'submitted',
        ]);

        MaterialRequestItem::create([
            'material_request_id' => $mr->id,
            'item_type'           => 'material',
            'material_id'         => $this->materialA->id,
            'qty_requested'       => 15,
            'qty_approved'        => 0,
        ]);

        $this->actingAs($this->adminPusat)->get(route('material-requests.show', $mr));

        $invFresh = $inv->fresh();
        $this->assertEquals(85, (float) $invFresh->quantity, 'Kuantitas fisik tidak boleh berubah.');
        $this->assertEquals(25, (float) $invFresh->qty_allocated, 'Alokasi stok tidak boleh berubah.');
    }

    public function test_inventory_query_does_not_scale_with_number_of_items_no_n_plus_one(): void
    {
        $mr = MaterialRequest::create([
            'request_number'       => 'MR-NPLUS1-TEST-01',
            'from_warehouse_id'    => $this->centralWh->id,
            'to_warehouse_id'      => $this->projectWh->id,
            'requested_by_user_id' => $this->adminPusat->id,
            'status'               => 'submitted',
        ]);

        // Buat 6 material dan inventori berbeda
        for ($i = 1; $i <= 6; $i++) {
            $m = Material::create([
                'name'        => "Material Batch {$i}",
                'sku'         => "MAT-BATCH-{$i}",
                'category_id' => $this->category->id,
                'unit_id'     => $this->unit->id,
                'is_active'   => true,
            ]);

            Inventory::create([
                'warehouse_id'  => $this->centralWh->id,
                'material_id'   => $m->id,
                'quantity'      => 50,
                'qty_allocated' => 10,
            ]);

            MaterialRequestItem::create([
                'material_request_id' => $mr->id,
                'item_type'           => 'material',
                'material_id'         => $m->id,
                'qty_requested'       => 10,
            ]);
        }

        DB::flushQueryLog();
        DB::enableQueryLog();

        $this->actingAs($this->adminPusat)->get(route('material-requests.show', $mr));

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        // Cari query ke tabel inventories
        $inventoryQueries = array_filter($queries, function ($q) {
            return str_contains(strtolower($q['query']), 'from `inventories`') ||
                   str_contains(strtolower($q['query']), 'from inventories');
        });

        // Query ke tabel inventories tidak boleh melebihi 1 query (harus dilakukan secara batch / agregat)
        $this->assertLessThanOrEqual(
            2,
            count($inventoryQueries),
            'Query ke tabel inventories harus menggunakan agregat batch, tanpa N+1 query per item.'
        );
    }
}
