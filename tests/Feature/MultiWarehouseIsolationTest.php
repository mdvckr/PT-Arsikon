<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Inventory;
use App\Models\Material;
use App\Models\Project;
use App\Models\StockMutation;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiWarehouseIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminPusat;
    protected User $userProyek;
    protected Warehouse $centralWarehouse;
    protected Warehouse $projectWarehouse;
    protected Material $material;
    protected Category $category;
    protected Unit $unit;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RoleAndPermissionSeeder::class);
        $this->seed(\Database\Seeders\MasterDataSeeder::class);

        $this->adminPusat = User::where('email', 'admin.pusat@arsikon.co.id')->firstOrFail();
        $this->userProyek = User::where('email', 'user.proyek@arsikon.co.id')->firstOrFail();

        $this->centralWarehouse = Warehouse::where('code', 'W-CENTRAL')->firstOrFail();
        $this->projectWarehouse = Warehouse::where('code', 'W-PRJ-001')->firstOrFail();

        $this->category = Category::where('type', 'material')->firstOrFail();
        $this->unit = Unit::firstOrFail();

        // Create material with stock ONLY in Project Warehouse (not in Central Warehouse)
        $this->material = Material::create([
            'sku' => 'BESI-TEST-01',
            'name' => 'Besi Beton Test 10mm',
            'category_id' => $this->category->id,
            'unit_id' => $this->unit->id,
            'is_active' => true,
        ]);

        Inventory::create([
            'warehouse_id' => $this->projectWarehouse->id,
            'material_id'  => $this->material->id,
            'quantity'     => 100,
            'min_stock'    => 10,
        ]);

        StockMutation::create([
            'warehouse_id'       => $this->projectWarehouse->id,
            'material_id'        => $this->material->id,
            'qty_change'         => 100,
            'qty_balance_after'  => 100,
            'reference_type'     => 'Initial Stock',
            'created_by_user_id' => $this->userProyek->id,
        ]);
    }

    public function test_admin_pusat_defaults_to_central_warehouse_and_does_not_see_project_stock(): void
    {
        // Admin Pusat visits Data Material without query param
        $response = $this->actingAs($this->adminPusat)->get(route('materials.index'));

        $response->assertStatus(200);
        // By default, Admin Pusat views Gudang Pusat (W-CENTRAL)
        // Material with stock only in W-PRJ-001 should NOT appear in default view
        $response->assertDontSee('BESI-TEST-01');
    }

    public function test_admin_pusat_can_filter_to_project_warehouse_to_see_stock(): void
    {
        // Admin Pusat filters by the project warehouse where stock exists
        $response = $this->actingAs($this->adminPusat)->get(route('materials.index', [
            'warehouse_id' => $this->projectWarehouse->id,
        ]));

        $response->assertStatus(200);
        $response->assertSee('BESI-TEST-01');
        $response->assertSee('100');
    }

    public function test_admin_pusat_can_view_consolidated_materials_when_explicitly_selected(): void
    {
        // Admin Pusat selects 'all' (Semua Gudang Konsolidasi)
        $response = $this->actingAs($this->adminPusat)->get(route('materials.index', [
            'warehouse_id' => 'all',
        ]));

        $response->assertStatus(200);
        $response->assertSee('BESI-TEST-01');
    }

    public function test_inventory_screen_scopes_to_central_warehouse_by_default_and_allows_filter(): void
    {
        // Default inventory visit for Admin Pusat -> scopes to Central Warehouse
        $responseDefault = $this->actingAs($this->adminPusat)->get(route('inventory.index'));
        $responseDefault->assertStatus(200);
        $responseDefault->assertDontSee('BESI-TEST-01');

        // Filter to project warehouse -> stock is visible
        $responseFilter = $this->actingAs($this->adminPusat)->get(route('inventory.index', [
            'warehouse_id' => $this->projectWarehouse->id,
        ]));
        $responseFilter->assertStatus(200);
        $responseFilter->assertSee('BESI-TEST-01');
        $responseFilter->assertSee('100');
    }

    public function test_project_user_cannot_access_or_view_other_unauthorized_warehouses(): void
    {
        // Create another isolated project warehouse
        $otherProject = Project::create([
            'code' => 'PRJ-ISO-99',
            'name' => 'Proyek Terisolasi',
            'status' => 'active',
            'start_date' => now()->toDateString(),
        ]);
        $otherWarehouse = Warehouse::create([
            'code' => 'W-ISO-99',
            'project_id' => $otherProject->id,
            'name' => 'Gudang Terisolasi 99',
            'type' => 'project',
            'is_central' => false,
            'is_active' => true,
        ]);

        $isolatedUser = User::create([
            'name' => 'User Proyek Terisolasi',
            'email' => 'isolated@arsikon.co.id',
            'password' => bcrypt('password123'),
        ]);
        $isolatedUser->syncRoles(['Admin Gudang Proyek']);
        $isolatedUser->warehouses()->sync([$otherWarehouse->id]);

        // When isolated user visits materials, they must NOT see BESI-TEST-01
        $response = $this->actingAs($isolatedUser)->get(route('materials.index'));
        $response->assertStatus(200);
        $response->assertDontSee('BESI-TEST-01');
    }
}
