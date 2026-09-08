<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\Material;
use App\Models\StockMutation;
use App\Models\Warehouse;
use App\Services\StockService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryAndStockTest extends TestCase
{
    use RefreshDatabase;

    protected StockService $stockService;
    protected Warehouse $centralWarehouse;
    protected Material $semenMaterial;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $this->stockService = new StockService();
        $this->centralWarehouse = Warehouse::where('is_central', true)->firstOrFail();
        $this->semenMaterial = Material::where('sku', 'MAT-SEM-001')->firstOrFail();
    }

    public function test_can_add_stock_and_record_mutation(): void
    {
        $inventory = $this->stockService->addStock(
            $this->centralWarehouse,
            $this->semenMaterial,
            150.0,
            'goods_receipt',
            1,
            null,
            'Penerimaan dari Supplier Tiga Roda'
        );

        $this->assertEquals(150.0, $inventory->qty_on_hand);
        $this->assertDatabaseHas('stock_mutations', [
            'warehouse_id' => $this->centralWarehouse->id,
            'material_id' => $this->semenMaterial->id,
            'reference_type' => 'goods_receipt',
            'qty_change' => 150.0,
            'qty_balance_after' => 150.0,
        ]);
    }

    public function test_can_deduct_stock_when_sufficient(): void
    {
        $this->stockService->addStock(
            $this->centralWarehouse,
            $this->semenMaterial,
            100.0,
            'goods_receipt',
            1
        );

        $inventory = $this->stockService->deductStock(
            $this->centralWarehouse,
            $this->semenMaterial,
            40.0,
            'distribution_ship',
            10,
            null,
            'Pengiriman ke Gudang Proyek A'
        );

        $this->assertEquals(60.0, $inventory->qty_on_hand);
        $this->assertDatabaseHas('stock_mutations', [
            'warehouse_id' => $this->centralWarehouse->id,
            'material_id' => $this->semenMaterial->id,
            'reference_type' => 'distribution_ship',
            'qty_change' => -40.0,
            'qty_balance_after' => 60.0,
        ]);
    }

    public function test_throws_exception_when_deducting_exceeds_available_stock(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Stok tidak mencukupi');

        $this->stockService->deductStock(
            $this->centralWarehouse,
            $this->semenMaterial,
            500.0, // Available is 0
            'distribution_ship',
            99
        );
    }
}
