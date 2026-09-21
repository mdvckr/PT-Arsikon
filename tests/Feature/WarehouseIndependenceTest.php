<?php

namespace Tests\Feature;

use App\Models\Distribution;
use App\Models\Material;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\DistributionService;
use App\Services\StockService;
use App\Services\ToolInventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Sprint 2 - Issue 3: Warehouse Independence
 * Memastikan query data (Inventory, Distributions) di-scope berdasarkan warehouse user.
 */
class WarehouseIndependenceTest extends TestCase
{
    use RefreshDatabase;

    protected Warehouse $centralWarehouse;
    protected Warehouse $projectWarehouseA;
    protected Warehouse $projectWarehouseB;
    protected User $adminPusat;
    protected User $adminProyekA;
    protected User $karyawan;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $this->centralWarehouse  = Warehouse::where('is_central', true)->firstOrFail();
        $this->projectWarehouseA = Warehouse::where('code', 'W-PRJ-001')->firstOrFail();
        $this->projectWarehouseB = Warehouse::where('code', 'W-PRJ-002')->firstOrFail();

        $this->adminPusat   = User::where('email', 'admin.pusat@arsikon.co.id')->firstOrFail();
        $this->adminProyekA = User::where('email', 'admin.proyek1@arsikon.co.id')->firstOrFail();
        $this->karyawan     = User::where('email', 'karyawan@arsikon.co.id')->firstOrFail();
    }

    // User model: accessibleWarehouseIds()

    public function test_admin_pusat_accessible_warehouse_ids_includes_all_warehouses(): void
    {
        $ids = $this->adminPusat->accessibleWarehouseIds();

        $this->assertContains($this->centralWarehouse->id, $ids);
        $this->assertContains($this->projectWarehouseA->id, $ids);
        $this->assertContains($this->projectWarehouseB->id, $ids);
    }

    public function test_admin_proyek_accessible_warehouse_ids_only_own_warehouse(): void
    {
        $ids = $this->adminProyekA->accessibleWarehouseIds();

        $this->assertContains($this->projectWarehouseA->id, $ids);
        $this->assertNotContains($this->centralWarehouse->id, $ids);
        $this->assertNotContains($this->projectWarehouseB->id, $ids);
    }

    public function test_karyawan_accessible_warehouse_ids_only_assigned_warehouse(): void
    {
        $ids = $this->karyawan->accessibleWarehouseIds();

        $this->assertContains($this->projectWarehouseA->id, $ids);
        $this->assertNotContains($this->centralWarehouse->id, $ids);
    }

    // InventoryController

    public function test_admin_pusat_can_access_inventory_page(): void
    {
        $this->actingAs($this->adminPusat)->get('/inventory')->assertOk();
    }

    public function test_admin_proyek_can_access_inventory_page(): void
    {
        $this->actingAs($this->adminProyekA)->get('/inventory')->assertOk();
    }

    public function test_karyawan_can_access_inventory_page(): void
    {
        $this->actingAs($this->karyawan)->get('/inventory')->assertOk();
    }

    // DistributionController: index scoping

    public function test_admin_pusat_can_see_all_distributions_in_index(): void
    {
        $stockService = new StockService();
        $material = Material::where('sku', 'MAT-SEM-001')->firstOrFail();
        $stockService->addStock($this->centralWarehouse, $material, 100, 'test');

        $distService = new DistributionService($stockService, new ToolInventoryService());
        $dist = $distService->create([
            'from_warehouse_id'   => $this->centralWarehouse->id,
            'to_warehouse_id'     => $this->projectWarehouseA->id,
            'material_request_id' => null,
            'tool_assignment_ids' => [],
            'delivery_date'       => now()->toDateString(),
            'driver_name'         => null,
            'vehicle_number'      => null,
            'notes'               => null,
            'items'               => [['type' => 'material', 'material_id' => $material->id, 'tool_id' => null, 'tool_assignment_id' => null, 'quantity' => 10]],
        ], $this->adminPusat->id);

        $this->actingAs($this->adminPusat)
            ->get('/distributions')
            ->assertOk()
            ->assertSee($dist->distribution_number);
    }

    public function test_admin_proyek_only_sees_own_warehouse_distributions_in_index(): void
    {
        $stockService = new StockService();
        $material = Material::where('sku', 'MAT-SEM-001')->firstOrFail();
        $stockService->addStock($this->centralWarehouse, $material, 200, 'test');

        $distService = new DistributionService($stockService, new ToolInventoryService());

        // Distribusi ke Proyek A - HARUS terlihat oleh Admin Proyek A
        $distA = $distService->create([
            'from_warehouse_id'   => $this->centralWarehouse->id,
            'to_warehouse_id'     => $this->projectWarehouseA->id,
            'material_request_id' => null,
            'tool_assignment_ids' => [],
            'delivery_date'       => now()->toDateString(),
            'driver_name'         => null,
            'vehicle_number'      => null,
            'notes'               => null,
            'items'               => [['type' => 'material', 'material_id' => $material->id, 'tool_id' => null, 'tool_assignment_id' => null, 'quantity' => 10]],
        ], $this->adminPusat->id);

        // Distribusi ke Proyek B - TIDAK boleh dilihat Admin Proyek A
        $distB = $distService->create([
            'from_warehouse_id'   => $this->centralWarehouse->id,
            'to_warehouse_id'     => $this->projectWarehouseB->id,
            'material_request_id' => null,
            'tool_assignment_ids' => [],
            'delivery_date'       => now()->toDateString(),
            'driver_name'         => null,
            'vehicle_number'      => null,
            'notes'               => null,
            'items'               => [['type' => 'material', 'material_id' => $material->id, 'tool_id' => null, 'tool_assignment_id' => null, 'quantity' => 10]],
        ], $this->adminPusat->id);

        $this->actingAs($this->adminProyekA)
            ->get('/distributions')
            ->assertOk()
            ->assertSee($distA->distribution_number)
            ->assertDontSee($distB->distribution_number);
    }

    public function test_distribution_create_page_accessible_by_admin_proyek(): void
    {
        $this->actingAs($this->adminProyekA)->get('/distributions/create')->assertOk();
    }
}
