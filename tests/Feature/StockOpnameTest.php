<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\Material;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\GoodsReceiptService;
use App\Services\StockOpnameService;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockOpnameTest extends TestCase
{
    use RefreshDatabase;

    protected StockOpnameService $opnameService;
    protected GoodsReceiptService $goodsReceiptService;
    protected Warehouse $centralWarehouse;
    protected User $adminUser;
    protected User $ownerUser;
    protected Supplier $supplier;
    protected Material $semenMaterial;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $stockService = new StockService();
        $this->opnameService = new StockOpnameService($stockService);
        $this->goodsReceiptService = new GoodsReceiptService($stockService);

        $this->centralWarehouse = Warehouse::where('is_central', true)->firstOrFail();
        $this->adminUser = User::where('email', 'admin.pusat@arsikon.co.id')->firstOrFail();
        $this->ownerUser = User::where('email', 'owner@arsikon.co.id')->firstOrFail();
        $this->supplier = Supplier::firstOrFail();
        $this->semenMaterial = Material::where('sku', 'MAT-SEM-001')->firstOrFail();

        // Populate Central Warehouse with 100 bags of Semen
        $this->goodsReceiptService->processGoodsReceipt(
            $this->supplier,
            $this->centralWarehouse,
            $this->adminUser,
            [
                ['material_id' => $this->semenMaterial->id, 'qty_received' => 100],
            ]
        );
    }

    public function test_full_stock_opname_workflow_and_automatic_adjustment(): void
    {
        // 1. Create Opname snapshot
        $opname = $this->opnameService->createOpname(
            $this->centralWarehouse,
            $this->adminUser,
            now()->toDateString(),
            'Opname Akhir Bulan Gudang Pusat'
        );

        $this->assertNotNull($opname);
        $this->assertEquals('draft', $opname->status);

        // System stock should still be 100
        $opnameItem = $opname->items->where('material_id', $this->semenMaterial->id)->first();
        $this->assertEquals(100, $opnameItem->qty_system);

        // 2. Submit Physical Count (Physical count is 95 -> difference -5)
        $submittedOpname = $this->opnameService->submitPhysicalCounts(
            $opname,
            [
                ['material_id' => $this->semenMaterial->id, 'qty_physical' => 95, 'notes' => '5 sak basah kena hujan']
            ]
        );

        $this->assertEquals('submitted', $submittedOpname->status);

        // Stock in database MUST NOT change before approval!
        $stockBeforeApprove = Inventory::where('warehouse_id', $this->centralWarehouse->id)
            ->where('material_id', $this->semenMaterial->id)
            ->value('qty_on_hand');
        $this->assertEquals(100, $stockBeforeApprove);

        // 3. Owner/Admin Approves Opname
        $approvedOpname = $this->opnameService->approveOpname(
            $submittedOpname,
            $this->ownerUser
        );

        $this->assertEquals('approved', $approvedOpname->status);

        // Stock in database MUST be adjusted to 95 after approval
        $stockAfterApprove = Inventory::where('warehouse_id', $this->centralWarehouse->id)
            ->where('material_id', $this->semenMaterial->id)
            ->value('qty_on_hand');
        $this->assertEquals(95, $stockAfterApprove);

        // Check Stock Mutation recorded
        $this->assertDatabaseHas('stock_mutations', [
            'warehouse_id' => $this->centralWarehouse->id,
            'material_id' => $this->semenMaterial->id,
            'reference_type' => 'opname_adjust',
            'qty_change' => -5,
            'qty_balance_after' => 95,
        ]);
    }
}
