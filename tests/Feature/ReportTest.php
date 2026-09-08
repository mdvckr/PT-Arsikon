<?php

namespace Tests\Feature;

use App\Models\Material;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\GoodsReceiptService;
use App\Services\ReportService;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    protected ReportService $reportService;
    protected GoodsReceiptService $goodsReceiptService;
    protected Warehouse $centralWarehouse;
    protected Warehouse $projectWarehouse;
    protected User $ownerUser;
    protected User $adminUser;
    protected User $projectUser;
    protected Supplier $supplier;
    protected Material $semenMaterial;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $stockService = new StockService();
        $this->reportService = new ReportService();
        $this->goodsReceiptService = new GoodsReceiptService($stockService);

        $this->centralWarehouse = Warehouse::where('is_central', true)->firstOrFail();
        $this->projectWarehouse = Warehouse::where('is_central', false)->firstOrFail();
        $this->ownerUser = User::where('email', 'owner@arsikon.co.id')->firstOrFail();
        $this->adminUser = User::where('email', 'admin.pusat@arsikon.co.id')->firstOrFail();
        $this->projectUser = User::where('email', 'user.proyek@arsikon.co.id')->firstOrFail();
        $this->supplier = Supplier::firstOrFail();
        $this->semenMaterial = Material::where('sku', 'MAT-SEM-001')->firstOrFail();

        // Add 50 bags of Semen to Central Warehouse (Min stock is 100 -> triggers low stock warning)
        $this->goodsReceiptService->processGoodsReceipt(
            $this->supplier,
            $this->centralWarehouse,
            $this->adminUser,
            [
                ['material_id' => $this->semenMaterial->id, 'qty_received' => 50],
            ]
        );
    }

    public function test_owner_can_generate_global_stock_report_with_low_stock_flag(): void
    {
        $report = $this->reportService->getStockReport($this->ownerUser);

        $this->assertNotEmpty($report);
        $semenItem = $report->firstWhere('sku', 'MAT-SEM-001');

        $this->assertNotNull($semenItem);
        $this->assertEquals(50, $semenItem['qty_on_hand']);
        $this->assertTrue($semenItem['is_low_stock']); // 50 < 100 min stock
    }

    public function test_stock_movement_report_tracks_receipt_mutation(): void
    {
        $movements = $this->reportService->getStockMovementReport($this->adminUser, $this->centralWarehouse);

        $this->assertNotEmpty($movements);
        $firstMovement = $movements->first();

        $this->assertEquals('goods_receipt', $firstMovement->reference_type);
        $this->assertEquals(50, $firstMovement->qty_change);
    }
}
