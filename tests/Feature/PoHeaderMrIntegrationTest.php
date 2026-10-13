<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Material;
use App\Models\MaterialRequest;
use App\Models\MaterialRequestItem;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Database\Seeders\MasterDataSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PoHeaderMrIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminPO;
    protected User $adminPusat;
    protected Supplier $supplier;
    protected Warehouse $centralWarehouse;
    protected Warehouse $projectWarehouse;
    protected Material $material;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(MasterDataSeeder::class);

        $this->adminPO = User::firstOrCreate(
            ['email' => 'adminpo.header.test@example.com'],
            ['name' => 'Admin PO Header Test', 'password' => bcrypt('password')]
        );
        $this->adminPO->syncRoles(['Admin PO']);

        $this->adminPusat = User::firstOrCreate(
            ['email' => 'adminpusat.header.test@example.com'],
            ['name' => 'Admin Pusat Header Test', 'password' => bcrypt('password')]
        );
        $this->adminPusat->syncRoles(['Admin Pusat']);

        $this->supplier = Supplier::first() ?? Supplier::create([
            'name' => 'PT Semen Jaya Mandiri',
            'phone' => '081299887766',
        ]);

        $this->centralWarehouse = Warehouse::where('is_central', true)->first() ?? Warehouse::create([
            'name' => 'Gudang Pusat',
            'code' => 'WH-PST-01',
            'is_central' => true,
        ]);

        $this->projectWarehouse = Warehouse::where('is_central', false)->first() ?? Warehouse::create([
            'name' => 'Gudang Proyek FK Teknik',
            'code' => 'WH-PRY-FK',
            'is_central' => false,
        ]);

        $category = Category::where('type', 'material')->first();
        $unit = Unit::first();

        $this->material = Material::firstOrCreate(
            ['sku' => 'MAT-HDR-01'],
            [
                'name' => 'Semen Gresik 50kg Proyek',
                'category_id' => $category->id,
                'unit_id' => $unit->id,
            ]
        );
    }

    public function test_create_po_form_displays_dari_po_lapangan_header_dropdown(): void
    {
        $mr = MaterialRequest::create([
            'request_number' => 'REQ-HDR-001',
            'from_warehouse_id' => null, // Kepada pengadaan
            'to_warehouse_id' => $this->projectWarehouse->id,
            'requested_by_user_id' => $this->adminPusat->id,
            'status' => 'approved',
            'approved_by_user_id' => $this->adminPusat->id,
            'approved_at' => now(),
        ]);

        $mrItem = MaterialRequestItem::create([
            'material_request_id' => $mr->id,
            'material_id' => $this->material->id,
            'qty_requested' => 50,
            'qty_approved' => 50,
            'qty_ordered' => 0,
            'fulfillment' => 'purchase',
        ]);

        $res = $this->actingAs($this->adminPO)->get(route('purchase-orders.create'));
        $res->assertStatus(200);
        $res->assertSee('Dari PO Lapangan (Permintaan Proyek)');
        $res->assertSee('material_request_select');
        $res->assertSee($mr->request_number);
    }

    public function test_store_po_persists_material_request_id_and_links_directly(): void
    {
        $mr = MaterialRequest::create([
            'request_number' => 'REQ-HDR-002',
            'from_warehouse_id' => null,
            'to_warehouse_id' => $this->projectWarehouse->id,
            'requested_by_user_id' => $this->adminPusat->id,
            'status' => 'approved',
            'approved_by_user_id' => $this->adminPusat->id,
            'approved_at' => now(),
        ]);

        $mrItem = MaterialRequestItem::create([
            'material_request_id' => $mr->id,
            'material_id' => $this->material->id,
            'qty_requested' => 40,
            'qty_approved' => 40,
            'qty_ordered' => 0,
            'fulfillment' => 'purchase',
        ]);

        $postData = [
            'supplier_id' => $this->supplier->id,
            'material_request_id' => $mr->id,
            'order_date' => now()->toDateString(),
            'delivery_warehouse_id' => $this->projectWarehouse->id,
            'items' => [
                [
                    'material_id' => $this->material->id,
                    'material_request_item_id' => $mrItem->id,
                    'quantity' => 40,
                    'unit_price' => 75000,
                ],
            ],
        ];

        $res = $this->actingAs($this->adminPO)->post(route('purchase-orders.store'), $postData);
        $res->assertRedirect();

        $po = PurchaseOrder::where('material_request_id', $mr->id)->latest()->first();
        $this->assertNotNull($po);
        $this->assertEquals($mr->id, $po->material_request_id);
        $this->assertEquals($mr->request_number, $po->materialRequest->request_number);
        $this->assertEquals(40, (float) $mrItem->fresh()->qty_ordered);

        // Show PO displays linked PO Lapangan
        $resShow = $this->actingAs($this->adminPO)->get(route('purchase-orders.show', $po));
        $resShow->assertStatus(200);
        $resShow->assertSee('Dari PO Lapangan:');
        $resShow->assertSee($mr->request_number);

        // Index PO displays linked PO Lapangan badge
        $resIndex = $this->actingAs($this->adminPO)->get(route('purchase-orders.index'));
        $resIndex->assertStatus(200);
        $resIndex->assertSee($mr->request_number);
    }
}
