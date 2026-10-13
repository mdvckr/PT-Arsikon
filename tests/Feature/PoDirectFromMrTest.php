<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Material;
use App\Models\MaterialRequest;
use App\Models\MaterialRequestItem;
use App\Models\ProcurementRequest;
use App\Models\ProcurementRequestItem;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Database\Seeders\MasterDataSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PoDirectFromMrTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminPusat;
    protected User $adminPO;
    protected Supplier $supplier;
    protected Warehouse $centralWarehouse;
    protected Warehouse $projectWarehouse;
    protected Material $materialA;
    protected Material $materialB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(MasterDataSeeder::class);

        $this->adminPusat = User::firstOrCreate(
            ['email' => 'admin.pusat.t7@example.com'],
            ['name' => 'Admin Pusat T7', 'password' => bcrypt('password')]
        );
        $this->adminPusat->syncRoles(['Admin Pusat']);

        $this->adminPO = User::firstOrCreate(
            ['email' => 'adminpo.t7@example.com'],
            ['name' => 'Admin PO T7', 'password' => bcrypt('password')]
        );
        $this->adminPO->syncRoles(['Admin PO']);

        $this->supplier = Supplier::first() ?? Supplier::create([
            'name' => 'PT Mitra Pengadaan T7',
            'phone' => '081234567800',
        ]);

        $this->centralWarehouse = Warehouse::where('is_central', true)->first() ?? Warehouse::create([
            'name' => 'Gudang Pusat T7',
            'code' => 'WH-PST-T7',
            'is_central' => true,
        ]);

        $this->projectWarehouse = Warehouse::where('is_central', false)->first() ?? Warehouse::create([
            'name' => 'Gudang Proyek T7',
            'code' => 'WH-PRY-T7',
            'is_central' => false,
        ]);

        $category = Category::where('type', 'material')->first();
        $unit = Unit::first();

        $this->materialA = Material::firstOrCreate(
            ['sku' => 'MAT-T7-01'],
            [
                'name' => 'Semen Padang 50kg T7',
                'category_id' => $category->id,
                'unit_id' => $unit->id,
            ]
        );

        $this->materialB = Material::firstOrCreate(
            ['sku' => 'MAT-T7-02'],
            [
                'name' => 'Besi Beton 10mm T7',
                'category_id' => $category->id,
                'unit_id' => $unit->id,
            ]
        );
    }

    public function test_mr_approved_shows_buat_po_button_and_populates_create_form(): void
    {
        $mr = MaterialRequest::create([
            'request_number' => 'MR-2026-T7-001',
            'from_warehouse_id' => $this->centralWarehouse->id,
            'to_warehouse_id' => $this->projectWarehouse->id,
            'requested_by_user_id' => $this->adminPusat->id,
            'status' => 'approved',
            'approved_by_user_id' => $this->adminPusat->id,
            'approved_at' => now(),
        ]);

        $itemPurchaseMat = MaterialRequestItem::create([
            'material_request_id' => $mr->id,
            'material_id' => $this->materialA->id,
            'qty_requested' => 100,
            'qty_approved' => 100,
            'qty_ordered' => 0,
            'fulfillment' => 'purchase',
        ]);

        $itemStockMat = MaterialRequestItem::create([
            'material_request_id' => $mr->id,
            'material_id' => $this->materialB->id,
            'qty_requested' => 50,
            'qty_approved' => 50,
            'qty_ordered' => 0,
            'fulfillment' => 'stock',
        ]);

        // Halaman detail PO Lapangan menampilkan tombol Buat PO
        $responseShow = $this->actingAs($this->adminPO)->get(route('material-requests.show', $mr));
        $responseShow->assertStatus(200);
        $responseShow->assertSee(route('purchase-orders.create', ['from_mr_id' => $mr->id]));

        // Buka form create PO dengan from_mr_id
        $responseCreate = $this->actingAs($this->adminPO)->get(route('purchase-orders.create', ['from_mr_id' => $mr->id]));
        $responseCreate->assertStatus(200);
        $responseCreate->assertSee($this->materialA->name);
        $responseCreate->assertSee($mr->toWarehouse->name);
    }

    public function test_store_po_directly_from_mr_increases_mr_item_qty_ordered_and_sets_delivery_warehouse(): void
    {
        $mr = MaterialRequest::create([
            'request_number' => 'MR-2026-T7-002',
            'from_warehouse_id' => $this->centralWarehouse->id,
            'to_warehouse_id' => $this->projectWarehouse->id,
            'requested_by_user_id' => $this->adminPusat->id,
            'status' => 'approved',
            'approved_by_user_id' => $this->adminPusat->id,
            'approved_at' => now(),
        ]);

        $itemPurchaseMat = MaterialRequestItem::create([
            'material_request_id' => $mr->id,
            'material_id' => $this->materialA->id,
            'qty_requested' => 80,
            'qty_approved' => 80,
            'qty_ordered' => 0,
            'fulfillment' => 'purchase',
        ]);

        $poData = [
            'supplier_id' => $this->supplier->id,
            'order_date' => now()->toDateString(),
            'items' => [
                [
                    'material_id' => $this->materialA->id,
                    'material_request_item_id' => $itemPurchaseMat->id,
                    'quantity' => 50,
                    'unit_price' => 65000,
                ],
            ],
        ];

        $res = $this->actingAs($this->adminPO)->post(route('purchase-orders.store'), $poData);
        $res->assertSessionHasNoErrors();

        $po = PurchaseOrder::latest('id')->first();
        $this->assertNotNull($po);
        $this->assertEquals($this->projectWarehouse->id, $po->delivery_warehouse_id);
        $this->assertEquals(50, (float) $itemPurchaseMat->fresh()->qty_ordered);
    }

    public function test_store_po_without_valid_source_is_rejected(): void
    {
        $poDataWithoutSource = [
            'supplier_id' => $this->supplier->id,
            'order_date' => now()->toDateString(),
            'items' => [
                [
                    'material_id' => $this->materialA->id,
                    'quantity' => 10,
                    'unit_price' => 65000,
                    // Tidak ada material_request_item_id dan tidak ada procurement_request_id
                ],
            ],
        ];

        $res = $this->actingAs($this->adminPO)->post(route('purchase-orders.store'), $poDataWithoutSource);
        $res->assertSessionHasErrors();
    }

    public function test_store_po_from_unapproved_mr_is_rejected(): void
    {
        $mrSubmitted = MaterialRequest::create([
            'request_number' => 'MR-2026-T7-UNAPP',
            'from_warehouse_id' => $this->centralWarehouse->id,
            'to_warehouse_id' => $this->projectWarehouse->id,
            'requested_by_user_id' => $this->adminPusat->id,
            'status' => 'submitted', // Belum approved!
        ]);

        $mrItem = MaterialRequestItem::create([
            'material_request_id' => $mrSubmitted->id,
            'material_id' => $this->materialA->id,
            'qty_requested' => 30,
            'qty_approved' => 0,
            'qty_ordered' => 0,
            'fulfillment' => 'purchase',
        ]);

        $poData = [
            'supplier_id' => $this->supplier->id,
            'order_date' => now()->toDateString(),
            'items' => [
                [
                    'material_id' => $this->materialA->id,
                    'material_request_item_id' => $mrItem->id,
                    'quantity' => 10,
                    'unit_price' => 65000,
                ],
            ],
        ];

        $res = $this->actingAs($this->adminPO)->post(route('purchase-orders.store'), $poData);
        $res->assertSessionHasErrors();
        $this->assertEquals(0, (float) $mrItem->fresh()->qty_ordered);
    }

    public function test_store_po_from_unapproved_pr_is_rejected(): void
    {
        $prSubmitted = ProcurementRequest::create([
            'pr_number' => 'PR-2026-T7-UNAPP',
            'requested_by' => $this->adminPusat->id,
            'status' => 'submitted', // Belum approved!
        ]);

        $prItem = ProcurementRequestItem::create([
            'procurement_request_id' => $prSubmitted->id,
            'material_id' => $this->materialA->id,
            'quantity' => 50,
            'qty_ordered' => 0,
        ]);

        $poData = [
            'supplier_id' => $this->supplier->id,
            'procurement_request_id' => $prSubmitted->id,
            'order_date' => now()->toDateString(),
            'items' => [
                [
                    'material_id' => $this->materialA->id,
                    'procurement_request_item_id' => $prItem->id,
                    'quantity' => 20,
                    'unit_price' => 65000,
                ],
            ],
        ];

        $res = $this->actingAs($this->adminPO)->post(route('purchase-orders.store'), $poData);
        $res->assertSessionHasErrors();
        $this->assertEquals(0, (float) $prItem->fresh()->qty_ordered);
    }

    public function test_create_po_redirects_if_mr_has_no_remaining_purchase_quota(): void
    {
        $mr = MaterialRequest::create([
            'request_number' => 'MR-2026-T7-EXHAUSTED',
            'from_warehouse_id' => $this->centralWarehouse->id,
            'to_warehouse_id' => $this->projectWarehouse->id,
            'requested_by_user_id' => $this->adminPusat->id,
            'status' => 'approved',
            'approved_by_user_id' => $this->adminPusat->id,
            'approved_at' => now(),
        ]);

        MaterialRequestItem::create([
            'material_request_id' => $mr->id,
            'material_id' => $this->materialA->id,
            'qty_requested' => 50,
            'qty_approved' => 50,
            'qty_ordered' => 50, // Kuota sudah habis dipesan!
            'fulfillment' => 'purchase',
        ]);

        $res = $this->actingAs($this->adminPO)->get(route('purchase-orders.create', ['from_mr_id' => $mr->id]));
        $res->assertRedirect(route('material-requests.show', $mr));
        $res->assertSessionHas('error');
    }

    public function test_store_po_for_tool_item_directly_from_mr(): void
    {
        $category = Category::where('type', 'tool')->first() ?? Category::create(['name' => 'Alat Berat', 'type' => 'tool']);
        $tool = \App\Models\Tool::firstOrCreate(
            ['code' => 'TL-T7-GENSET'],
            [
                'name' => 'Genset 5000W T7',
                'category_id' => $category->id,
                'is_active' => true,
            ]
        );

        $mr = MaterialRequest::create([
            'request_number' => 'MR-2026-T7-TOOL',
            'from_warehouse_id' => $this->centralWarehouse->id,
            'to_warehouse_id' => $this->projectWarehouse->id,
            'requested_by_user_id' => $this->adminPusat->id,
            'status' => 'approved',
            'approved_by_user_id' => $this->adminPusat->id,
            'approved_at' => now(),
        ]);

        $mrItemTool = MaterialRequestItem::create([
            'material_request_id' => $mr->id,
            'item_type' => 'tool',
            'tool_id' => $tool->id,
            'qty_requested' => 2,
            'qty_approved' => 2,
            'qty_ordered' => 0,
            'fulfillment' => 'purchase',
        ]);

        $poData = [
            'supplier_id' => $this->supplier->id,
            'order_date' => now()->toDateString(),
            'items' => [
                [
                    'tool_id' => $tool->id,
                    'material_request_item_id' => $mrItemTool->id,
                    'quantity' => 2,
                    'unit_price' => 7500000,
                ],
            ],
        ];

        $res = $this->actingAs($this->adminPO)->post(route('purchase-orders.store'), $poData);
        $res->assertSessionHasNoErrors();

        $po = PurchaseOrder::latest('id')->first();
        $this->assertNotNull($po);
        $this->assertEquals($this->projectWarehouse->id, $po->delivery_warehouse_id);
        $this->assertEquals(2, (float) $mrItemTool->fresh()->qty_ordered);
        $this->assertEquals($tool->id, $po->items()->first()->tool_id);
    }

    public function test_pr_path_still_works_for_central_restock(): void
    {
        $prApproved = ProcurementRequest::create([
            'pr_number' => 'PR-2026-T7-RESTOCK',
            'requested_by' => $this->adminPusat->id,
            'status' => 'approved',
            'approved_by' => $this->adminPusat->id,
            'approved_at' => now(),
        ]);

        $prItem = ProcurementRequestItem::create([
            'procurement_request_id' => $prApproved->id,
            'material_id' => $this->materialB->id,
            'quantity' => 100,
            'qty_ordered' => 0,
        ]);

        $poData = [
            'supplier_id' => $this->supplier->id,
            'procurement_request_id' => $prApproved->id,
            'order_date' => now()->toDateString(),
            'items' => [
                [
                    'material_id' => $this->materialB->id,
                    'procurement_request_item_id' => $prItem->id,
                    'quantity' => 40,
                    'unit_price' => 85000,
                ],
            ],
        ];

        $res = $this->actingAs($this->adminPO)->post(route('purchase-orders.store'), $poData);
        $res->assertSessionHasNoErrors();

        $this->assertEquals(40, (float) $prItem->fresh()->qty_ordered);
        $this->assertEquals('partially_ordered', $prApproved->fresh()->status);
    }
}
