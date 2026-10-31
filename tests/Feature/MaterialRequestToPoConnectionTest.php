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

class MaterialRequestToPoConnectionTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminPusat;
    protected User $adminPO;
    protected User $siteAdmin;
    protected Warehouse $projectWarehouse;
    protected Supplier $supplier;
    protected Material $material;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(MasterDataSeeder::class);

        $this->projectWarehouse = Warehouse::create([
            'code' => 'W-PROJ-TEST',
            'name' => 'Gudang Proyek FK Teknik',
            'type' => 'project',
            'is_central' => false,
            'address' => 'Site Proyek Kampus',
        ]);

        $this->adminPusat = User::firstOrCreate(
            ['email' => 'admin.pusat.test@example.com'],
            ['name' => 'Admin Pusat Test', 'password' => bcrypt('password')]
        );
        $this->adminPusat->syncRoles(['Admin Pusat']);

        $this->adminPO = User::firstOrCreate(
            ['email' => 'admin.po.test@example.com'],
            ['name' => 'Admin PO Test', 'password' => bcrypt('password')]
        );
        $this->adminPO->syncRoles(['Admin PO']);

        $this->siteAdmin = User::firstOrCreate(
            ['email' => 'admin.site.test@example.com'],
            ['name' => 'Admin Gudang Proyek Test', 'password' => bcrypt('password')]
        );
        $this->siteAdmin->syncRoles(['Admin Gudang Proyek']);
        $this->siteAdmin->warehouses()->attach($this->projectWarehouse->id);

        $this->supplier = Supplier::first() ?? Supplier::create([
            'name' => 'Indo Jaya',
            'phone' => '081234567890',
        ]);

        $category = Category::where('type', 'material')->first();
        $unit = Unit::first();
        $this->material = Material::firstOrCreate(
            ['sku' => 'MAT-TEST-CONN'],
            [
                'name' => 'Kencleng 30mm x 12m',
                'category_id' => $category->id,
                'unit_id' => $unit->id,
                'min_stock_central' => 5,
            ]
        );
    }

    public function test_po_lapangan_with_no_source_warehouse_defaults_to_purchase_fulfillment(): void
    {
        // 1. Ajukan PO Lapangan tanpa gudang sumber (ditujukan ke Pengadaan)
        $mrData = [
            'warehouse_id' => $this->projectWarehouse->id,
            'source_warehouse_id' => null,
            'quantities' => [
                $this->material->id => 10,
            ],
            'notes' => 'Kebutuhan mendesak proyek',
        ];

        $response = $this->actingAs($this->siteAdmin)
            ->post(route('material-requests.store'), $mrData);

        $response->assertSessionHasNoErrors();
        $mr = MaterialRequest::where('to_warehouse_id', $this->projectWarehouse->id)->latest('id')->first();
        $this->assertNotNull($mr);
        $this->assertStringStartsWith('REQ-', $mr->request_number);

        $mrItem = $mr->items()->first();
        $this->assertNotNull($mrItem);
        $this->assertEquals('purchase', $mrItem->fulfillment);

        // 2. Setujui PO Lapangan oleh Admin Pusat
        $this->flushSession();
        $approveRes = $this->actingAs($this->adminPusat)
            ->post(route('material-requests.approve', $mr));
        $approveRes->assertSessionHas('success');

        $this->assertEquals('approved', $mr->fresh()->status);
        $this->assertEquals('purchase', $mrItem->fresh()->fulfillment);

        // 3. Halaman show PO Lapangan menampilkan tombol Terbitkan PO Vendor
        $this->flushSession();
        $showRes = $this->actingAs($this->adminPO)
            ->get(route('material-requests.show', $mr));
        $showRes->assertStatus(200);
        $showRes->assertSee(route('purchase-orders.create', ['from_mr_id' => $mr->id]));

        // 4. Buka form Buat PO, pastikan REQ dari PO Lapangan muncul di dropdown dan data map
        $createRes = $this->actingAs($this->adminPO)
            ->get(route('purchase-orders.create'));
        $createRes->assertStatus(200);
        $createRes->assertSee($mr->request_number);
        $createRes->assertSee('Gudang Proyek FK Teknik');

        // 5. Buka form Buat PO langsung dengan parameter from_mr_id
        $createFromMrRes = $this->actingAs($this->adminPO)
            ->get(route('purchase-orders.create', ['from_mr_id' => $mr->id]));
        $createFromMrRes->assertStatus(200);
        $createFromMrRes->assertSee($this->material->name);
        $createFromMrRes->assertSee($mr->request_number);

        // 6. Buat PO Vendor terhubung ke REQ PO Lapangan
        $poData = [
            'supplier_id' => $this->supplier->id,
            'material_request_id' => $mr->id,
            'delivery_warehouse_id' => $this->projectWarehouse->id,
            'order_date' => now()->toDateString(),
            'items' => [
                [
                    'material_id' => $this->material->id,
                    'material_request_item_id' => $mrItem->id,
                    'quantity' => 10,
                    'unit_price' => 150000,
                ],
            ],
        ];

        $storeRes = $this->actingAs($this->adminPO)
            ->post(route('purchase-orders.store'), $poData);
        $storeRes->assertSessionHasNoErrors();

        $po = PurchaseOrder::where('material_request_id', $mr->id)->first();
        $this->assertNotNull($po);
        $this->assertEquals($mr->id, $po->material_request_id);
        $this->assertEquals(10, (float) $mrItem->fresh()->qty_ordered);
    }
}
