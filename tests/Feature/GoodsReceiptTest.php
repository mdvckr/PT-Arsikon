<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\Material;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\GoodsReceiptService;
use App\Services\StockService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoodsReceiptTest extends TestCase
{
    use RefreshDatabase;

    protected GoodsReceiptService $goodsReceiptService;
    protected Warehouse $centralWarehouse;
    protected Warehouse $projectWarehouse;
    protected Supplier $supplier;
    protected User $adminUser;
    protected Material $semenMaterial;
    protected Material $besiMaterial;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $this->goodsReceiptService = new GoodsReceiptService(new StockService());
        $this->centralWarehouse = Warehouse::where('is_central', true)->firstOrFail();
        $this->projectWarehouse = Warehouse::where('is_central', false)->firstOrFail();
        $this->supplier = Supplier::firstOrFail();
        $this->adminUser = User::where('email', 'admin.pusat@arsikon.co.id')->firstOrFail();
        $this->semenMaterial = Material::where('sku', 'MAT-SEM-001')->firstOrFail();
        $this->besiMaterial = Material::where('sku', 'MAT-BES-001')->firstOrFail();
    }

    public function test_can_process_goods_receipt_at_central_warehouse(): void
    {
        $receipt = $this->goodsReceiptService->processGoodsReceipt(
            $this->supplier,
            $this->centralWarehouse,
            $this->adminUser,
            [
                ['material_id' => $this->semenMaterial->id, 'qty_received' => 200, 'unit_price' => 65000],
                ['material_id' => $this->besiMaterial->id, 'qty_received' => 50, 'unit_price' => 120000],
            ],
            now()->toDateString(),
            'Penerimaan Barang Dari PT Semen Indonesia'
        );

        $this->assertNotNull($receipt);
        $this->assertStringStartsWith('GR-', $receipt->receipt_number);
        $this->assertCount(2, $receipt->items);

        // Check stock updated
        $semenInventory = Inventory::where('warehouse_id', $this->centralWarehouse->id)
            ->where('material_id', $this->semenMaterial->id)
            ->first();

        $this->assertEquals(200, $semenInventory->qty_on_hand);
    }

    public function test_cannot_process_goods_receipt_at_project_warehouse(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('hanya boleh dilakukan di Gudang Pusat');

        $this->goodsReceiptService->processGoodsReceipt(
            $this->supplier,
            $this->projectWarehouse, // Invalid warehouse for supplier receipt
            $this->adminUser,
            [
                ['material_id' => $this->semenMaterial->id, 'qty_received' => 50],
            ]
        );
    }
}
