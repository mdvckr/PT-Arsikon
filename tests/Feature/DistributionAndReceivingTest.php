<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\Material;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\DistributionService;
use App\Services\MaterialRequestService;
use App\Services\StockService;
use App\Services\ToolInventoryService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DistributionAndReceivingTest extends TestCase
{
    use RefreshDatabase;

    protected DistributionService $distributionService;
    protected MaterialRequestService $requestService;
    protected StockService $stockService;

    protected Warehouse $centralWarehouse;
    protected Warehouse $projectWarehouse;
    protected User $adminUser;
    protected User $projectUser;
    protected User $adminPOUser;
    protected Supplier $supplier;
    protected Material $semenMaterial;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $this->stockService = new StockService();
        $toolInventoryService = new ToolInventoryService();
        $this->distributionService = new DistributionService($this->stockService, $toolInventoryService);
        $this->requestService = new MaterialRequestService();

        $this->centralWarehouse = Warehouse::where('is_central', true)->firstOrFail();
        $this->projectWarehouse = Warehouse::where('is_central', false)->firstOrFail();
        $this->adminUser = User::where('email', 'admin.pusat@arsikon.co.id')->firstOrFail();
        $this->projectUser = User::where('email', 'admin.proyek1@arsikon.co.id')->firstOrFail();
        $this->adminPOUser = User::where('email', 'admin.po@arsikon.co.id')->firstOrFail();
        
        // Create supplier since not seeded
        $this->supplier = Supplier::firstOrCreate(
            ['code' => 'SUP-TEST'],
            ['code' => 'SUP-TEST', 'name' => 'Supplier Test', 'contact_person' => 'Test', 'phone' => '08123456789', 'address' => 'Test Address', 'is_active' => true]
        );
        $this->semenMaterial = Material::where('sku', 'MAT-SEM-001')->firstOrFail();

        // Stock Central with 500 bags of Semen
        $this->stockService->addStock(
            $this->centralWarehouse,
            $this->semenMaterial,
            500.0,
            'goods_receipt',
            1,
            $this->adminUser->id,
            'Penerimaan dari Supplier Tiga Roda'
        );
    }

    public function test_full_distribution_and_project_receiving_flow(): void
    {
        // 1. Project User creates request for 200 bags
        $request = $this->requestService->createRequest(
            $this->projectWarehouse,
            $this->projectUser,
            [['material_id' => $this->semenMaterial->id, 'qty_requested' => 200]]
        );

        // 2. Admin approves request
        $approvedRequest = $this->requestService->approveRequest($request, $this->adminUser);

        // 3. Admin creates + ships 200 bags
        $distribution = $this->distributionService->create([
            'material_request_id'  => $approvedRequest->id,
            'from_warehouse_id'    => $this->centralWarehouse->id,
            'to_warehouse_id'      => $this->projectWarehouse->id,
            'delivery_date'        => now()->toDateString(),
            'driver_name'          => 'Budi',
            'vehicle_number'       => 'B 1234 CD',
            'items' => [
                ['type' => 'material', 'material_id' => $this->semenMaterial->id, 'quantity' => 200],
            ],
        ], $this->adminUser->id);

        $this->distributionService->ship($distribution, $this->adminUser->id);

        $distribution->refresh();
        $this->assertEquals('in_transit', $distribution->status);

        // Central stock: seeder adds 500, setUp adds 500 = 1000 initial, minus 200 shipped = 800
        $centralStock = Inventory::where('warehouse_id', $this->centralWarehouse->id)
            ->where('material_id', $this->semenMaterial->id)
            ->value('quantity');
        $this->assertEquals(800, $centralStock);

        // Project in_transit stock should be 200
        $projectInTransit = Inventory::where('warehouse_id', $this->projectWarehouse->id)
            ->where('material_id', $this->semenMaterial->id)
            ->value('qty_in_transit');
        $this->assertEquals(200, $projectInTransit);

        // 4. Project User receives 195 bags good, 5 bags damaged (partial receive)
        $completedDistribution = $this->distributionService->receive(
            $distribution,
            [
                ['distribution_item_id' => $distribution->items->first()->id, 'received_quantity' => 195, 'qty_damaged_or_lost' => 5]
            ],
            $this->projectUser->id
        );

        $this->assertEquals('completed', $completedDistribution->status);

        // Project stock on hand: seeder adds ~38, plus 195 received = 233
        $projectStock = Inventory::where('warehouse_id', $this->projectWarehouse->id)
            ->where('material_id', $this->semenMaterial->id)
            ->value('quantity');
        $this->assertEquals(233, $projectStock);

        // Project in_transit stock should now be 0
        $projectInTransitAfter = Inventory::where('warehouse_id', $this->projectWarehouse->id)
            ->where('material_id', $this->semenMaterial->id)
            ->value('qty_in_transit');
        $this->assertEquals(0, $projectInTransitAfter);
    }

    public function test_print_surat_jalan_page_displayed_for_in_transit(): void
    {
        $request = $this->requestService->createRequest(
            $this->projectWarehouse,
            $this->projectUser,
            [['material_id' => $this->semenMaterial->id, 'qty_requested' => 100]]
        );

        $approvedRequest = $this->requestService->approveRequest($request, $this->adminUser);

        $distribution = $this->distributionService->create([
            'material_request_id'  => $approvedRequest->id,
            'from_warehouse_id'    => $this->centralWarehouse->id,
            'to_warehouse_id'      => $this->projectWarehouse->id,
            'delivery_date'        => now()->toDateString(),
            'driver_name'          => 'Budi',
            'vehicle_number'       => 'B 1234 CD',
            'items' => [
                ['type' => 'material', 'material_id' => $this->semenMaterial->id, 'quantity' => 100],
            ],
        ], $this->adminUser->id);

        $this->distributionService->ship($distribution, $this->adminUser->id);

        $response = $this->actingAs($this->adminUser)
            ->get(route('distributions.print', $distribution));

        $response->assertOk();
        $response->assertSee('Surat Jalan');
        $response->assertSee($distribution->distribution_number);
        $response->assertSee($this->semenMaterial->name);
    }

    public function test_print_surat_jalan_page_accessible_for_viewer(): void
    {
        $request = $this->requestService->createRequest(
            $this->projectWarehouse,
            $this->projectUser,
            [['material_id' => $this->semenMaterial->id, 'qty_requested' => 100]]
        );

        $approvedRequest = $this->requestService->approveRequest($request, $this->adminUser);

        $distribution = $this->distributionService->create([
            'material_request_id'  => $approvedRequest->id,
            'from_warehouse_id'    => $this->centralWarehouse->id,
            'to_warehouse_id'      => $this->projectWarehouse->id,
            'delivery_date'        => now()->toDateString(),
            'driver_name'          => 'Budi',
            'vehicle_number'       => 'B 1234 CD',
            'items' => [
                ['type' => 'material', 'material_id' => $this->semenMaterial->id, 'quantity' => 100],
            ],
        ], $this->adminUser->id);

        $this->distributionService->ship($distribution, $this->adminUser->id);

        // projectUser memiliki view distributions, jadi harus bisa mengakses halaman cetak
        $response = $this->actingAs($this->projectUser)
            ->get(route('distributions.print', $distribution));

        $response->assertOk();
    }

    public function test_admin_po_can_access_print_surat_jalan_page(): void
    {
        $request = $this->requestService->createRequest(
            $this->projectWarehouse,
            $this->projectUser,
            [['material_id' => $this->semenMaterial->id, 'qty_requested' => 100]]
        );

        $approvedRequest = $this->requestService->approveRequest($request, $this->adminUser);

        $distribution = $this->distributionService->create([
            'material_request_id'  => $approvedRequest->id,
            'from_warehouse_id'    => $this->centralWarehouse->id,
            'to_warehouse_id'      => $this->projectWarehouse->id,
            'delivery_date'        => now()->toDateString(),
            'driver_name'          => 'Budi',
            'vehicle_number'       => 'B 1234 CD',
            'items' => [
                ['type' => 'material', 'material_id' => $this->semenMaterial->id, 'quantity' => 100],
            ],
        ], $this->adminUser->id);

        $this->distributionService->ship($distribution, $this->adminUser->id);

        $this->assertTrue($this->adminPOUser->can('view distributions'));

        $response = $this->actingAs($this->adminPOUser)
            ->get(route('distributions.print', $distribution));

        $response->assertOk();
    }
}
