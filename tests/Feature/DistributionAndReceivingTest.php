<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\Material;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\DistributionService;
use App\Services\GoodsReceiptService;
use App\Services\MaterialRequestService;
use App\Services\StockService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DistributionAndReceivingTest extends TestCase
{
    use RefreshDatabase;

    protected DistributionService $distributionService;
    protected MaterialRequestService $requestService;
    protected GoodsReceiptService $goodsReceiptService;

    protected Warehouse $centralWarehouse;
    protected Warehouse $projectWarehouse;
    protected User $adminUser;
    protected User $projectUser;
    protected Supplier $supplier;
    protected Material $semenMaterial;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $stockService = new StockService();
        $this->distributionService = new DistributionService($stockService);
        $this->requestService = new MaterialRequestService();
        $this->goodsReceiptService = new GoodsReceiptService($stockService);

        $this->centralWarehouse = Warehouse::where('is_central', true)->firstOrFail();
        $this->projectWarehouse = Warehouse::where('is_central', false)->firstOrFail();
        $this->adminUser = User::where('email', 'admin.pusat@arsikon.co.id')->firstOrFail();
        $this->projectUser = User::where('email', 'user.proyek@arsikon.co.id')->firstOrFail();
        $this->supplier = Supplier::firstOrFail();
        $this->semenMaterial = Material::where('sku', 'MAT-SEM-001')->firstOrFail();

        // Stock Central with 500 bags of Semen
        $this->goodsReceiptService->processGoodsReceipt(
            $this->supplier,
            $this->centralWarehouse,
            $this->adminUser,
            [
                ['material_id' => $this->semenMaterial->id, 'qty_received' => 500],
            ]
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

        // 3. Admin ships 200 bags
        $distribution = $this->distributionService->shipDistribution(
            $approvedRequest,
            $this->adminUser,
            [['material_id' => $this->semenMaterial->id, 'qty_shipped' => 200]]
        );

        $this->assertEquals('in_transit', $distribution->status);

        // Central stock should now be 300 (500 - 200)
        $centralStock = Inventory::where('warehouse_id', $this->centralWarehouse->id)
            ->where('material_id', $this->semenMaterial->id)
            ->value('qty_on_hand');
        $this->assertEquals(300, $centralStock);

        // Project in_transit stock should be 200
        $projectInTransit = Inventory::where('warehouse_id', $this->projectWarehouse->id)
            ->where('material_id', $this->semenMaterial->id)
            ->value('qty_in_transit');
        $this->assertEquals(200, $projectInTransit);

        // 4. Project User receives 195 bags good, 5 bags damaged (partial receive)
        $completedDistribution = $this->distributionService->receiveDistribution(
            $distribution,
            $this->projectUser,
            [
                ['material_id' => $this->semenMaterial->id, 'qty_received' => 195, 'qty_damaged_or_lost' => 5]
            ],
            '5 sak semen robek di jalan'
        );

        $this->assertEquals('completed', $completedDistribution->status);

        // Project stock on hand should now be 195
        $projectStock = Inventory::where('warehouse_id', $this->projectWarehouse->id)
            ->where('material_id', $this->semenMaterial->id)
            ->value('qty_on_hand');
        $this->assertEquals(195, $projectStock);

        // Project in_transit stock should now be 0
        $projectInTransitAfter = Inventory::where('warehouse_id', $this->projectWarehouse->id)
            ->where('material_id', $this->semenMaterial->id)
            ->value('qty_in_transit');
        $this->assertEquals(0, $projectInTransitAfter);
    }
}
