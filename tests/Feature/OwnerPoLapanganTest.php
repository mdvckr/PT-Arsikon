<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Material;
use App\Models\MaterialRequest;
use App\Models\MaterialRequestItem;
use App\Models\Project;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OwnerPoLapanganTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected User $adminPengadaan;
    protected User $siteEngineer;
    protected Project $projectA;
    protected Project $projectB;
    protected Warehouse $warehouseA;
    protected Warehouse $warehouseB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);

        $this->owner = User::where('email', 'owner@arsikon.co.id')->firstOrFail();
        $this->adminPengadaan = User::where('email', 'admin.pengadaan@arsikon.co.id')->firstOrFail();
        $this->siteEngineer = User::where('email', 'engineer.prj1@arsikon.co.id')->firstOrFail();

        $this->projectA = Project::where('code', 'PRJ-001')->firstOrFail();
        $this->projectB = Project::where('code', 'PRJ-002')->firstOrFail();
        $this->warehouseA = Warehouse::where('code', 'W-PRJ-001')->firstOrFail();
        $this->warehouseB = Warehouse::where('code', 'W-PRJ-002')->firstOrFail();
    }

    public function test_owner_sees_project_filter_and_can_filter_by_project(): void
    {
        $mrA = MaterialRequest::create([
            'request_number' => 'MR-PRJ-A-001',
            'to_warehouse_id' => $this->warehouseA->id,
            'requested_by_user_id' => $this->siteEngineer->id,
            'status' => 'submitted',
        ]);

        $mrB = MaterialRequest::create([
            'request_number' => 'MR-PRJ-B-001',
            'to_warehouse_id' => $this->warehouseB->id,
            'requested_by_user_id' => $this->siteEngineer->id,
            'status' => 'submitted',
        ]);

        // 1. Owner sees project filter dropdown
        $response = $this->actingAs($this->owner)->get(route('material-requests.index'));
        $response->assertOk();
        $response->assertSee('name="project_id"', false);
        $response->assertSee($this->projectA->name);
        $response->assertSee($this->projectB->name);
        $response->assertSee('MR-PRJ-A-001');
        $response->assertSee('MR-PRJ-B-001');

        // 2. Owner does NOT see Edit button in table
        $response->assertDontSee(route('material-requests.edit', $mrA));
        $response->assertDontSee(route('material-requests.edit', $mrB));

        // 3. Filter by project A
        $responseA = $this->actingAs($this->owner)->get(route('material-requests.index', ['project_id' => $this->projectA->id]));
        $responseA->assertOk();
        $responseA->assertSee('MR-PRJ-A-001');
        $responseA->assertDontSee('MR-PRJ-B-001');

        // 4. Filter by project B
        $responseB = $this->actingAs($this->owner)->get(route('material-requests.index', ['project_id' => $this->projectB->id]));
        $responseB->assertOk();
        $responseB->assertSee('MR-PRJ-B-001');
        $responseB->assertDontSee('MR-PRJ-A-001');
    }

    public function test_non_owner_does_not_see_project_filter(): void
    {
        $response = $this->actingAs($this->siteEngineer)->get(route('material-requests.index'));
        $response->assertOk();
        $response->assertDontSee('name="project_id"', false);
    }

    public function test_owner_does_not_see_terbitkan_po_vendor_and_edit_on_detail(): void
    {
        $unit = Unit::firstOrCreate(['name' => 'Pcs', 'code' => 'pcs'], ['is_decimal' => false]);
        $cat = Category::firstOrCreate(['name' => 'Bahan', 'code' => 'BHN'], ['type' => 'material']);
        $mat = Material::create([
            'name' => 'Semen Gresik 40kg',
            'sku' => 'MAT-SMN-40',
            'category_id' => $cat->id,
            'unit_id' => $unit->id,
            'is_active' => true,
        ]);

        $mr = MaterialRequest::create([
            'request_number' => 'MR-OWNER-TEST-001',
            'to_warehouse_id' => $this->warehouseA->id,
            'requested_by_user_id' => $this->siteEngineer->id,
            'status' => 'approved',
        ]);

        MaterialRequestItem::create([
            'material_request_id' => $mr->id,
            'material_id' => $mat->id,
            'qty_requested' => 100,
            'qty_approved' => 100,
            'qty_ordered' => 0,
            'fulfillment' => 'purchase',
        ]);

        // When Owner views the detail page
        $responseOwner = $this->actingAs($this->owner)->get(route('material-requests.show', $mr));
        $responseOwner->assertOk();
        $responseOwner->assertDontSee('Terbitkan PO Vendor');
        $responseOwner->assertDontSee('Terbitkan PO Vendor Sekarang');
        $responseOwner->assertDontSee('Edit PO Lapangan');

        // When Admin Pengadaan views the detail page, Terbitkan PO Vendor IS visible
        $responseAdmin = $this->actingAs($this->adminPengadaan)->get(route('material-requests.show', $mr));
        $responseAdmin->assertOk();
        $responseAdmin->assertSee('Terbitkan PO Vendor');
    }
}
