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
        $this->semenMaterial = Material::firstOrCreate(
            ['sku' => 'MAT-SEM-001'],
            ['name' => 'Semen Portland 50kg', 'category_id' => \App\Models\Category::first()->id, 'unit_id' => \App\Models\Unit::first()->id]
        );
        $this->besiMaterial = Material::firstOrCreate(
            ['sku' => 'MAT-BES-001'],
            ['name' => 'Besi Beton 10mm', 'category_id' => \App\Models\Category::first()->id, 'unit_id' => \App\Models\Unit::first()->id]
        );

        // Reset inventory for test isolation
        Inventory::where('warehouse_id', $this->centralWarehouse->id)
            ->whereIn('material_id', [$this->semenMaterial->id, $this->besiMaterial->id])
            ->delete();
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

    public function test_can_receive_po_full_at_central_warehouse(): void
    {
        // 1. Create a PO
        $po = \App\Models\PurchaseOrder::create([
            'po_number'         => 'PO-2026-TEST1',
            'supplier_id'       => $this->supplier->id,
            'supplier_name'     => $this->supplier->name,
            'created_by'        => $this->adminUser->id,
            'status'            => 'sent',
            'order_date'        => now()->toDateString(),
            'expected_delivery' => now()->addDays(3)->toDateString(),
            'total_amount'      => 6500000,
        ]);

        $poItem = \App\Models\PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'material_id'       => $this->semenMaterial->id,
            'quantity'          => 100,
            'received_qty'      => 0,
            'unit_price'        => 65000,
            'total_price'       => 6500000,
        ]);

        // 2. Create Goods Receipt for 100 bags (full)
        $receipt = $this->goodsReceiptService->create([
            'purchase_order_id' => $po->id,
            'supplier_id'       => $this->supplier->id,
            'warehouse_id'      => $this->centralWarehouse->id,
            'received_at'       => now()->toDateString(),
            'invoice_number'    => 'INV-SUP-100',
            'items'             => [
                [
                    'material_id'            => $this->semenMaterial->id,
                    'purchase_order_item_id' => $poItem->id,
                    'quantity'               => 100,
                    'unit_price'             => 65000,
                ],
            ],
        ], $this->adminUser->id);

        $this->assertEquals('draft', $receipt->status);

        // 3. Confirm Goods Receipt
        $this->actingAs($this->adminUser);
        $response = $this->post(route('goods-receipts.confirm', $receipt));
        $response->assertSessionHas('success');

        $receipt->refresh();
        $this->assertEquals('confirmed', $receipt->status);

        // Verify PO status updated to received
        $po->refresh();
        $this->assertEquals('received', $po->status);
        $this->assertEquals(100, $poItem->fresh()->received_qty);

        // Verify stock updated
        $semenStock = Inventory::where('warehouse_id', $this->centralWarehouse->id)
            ->where('material_id', $this->semenMaterial->id)
            ->value('quantity');
        $this->assertEquals(100, $semenStock);
    }

    public function test_can_receive_po_partial_at_project_warehouse(): void
    {
        $adminProyek = User::where('email', 'admin.proyek1@arsikon.co.id')->firstOrFail();

        // 1. Create a PO for 100 bags
        $po = \App\Models\PurchaseOrder::create([
            'po_number'         => 'PO-2026-TEST2',
            'supplier_id'       => $this->supplier->id,
            'supplier_name'     => $this->supplier->name,
            'created_by'        => $this->adminUser->id,
            'status'            => 'sent',
            'order_date'        => now()->toDateString(),
            'total_amount'      => 6500000,
        ]);

        $poItem = \App\Models\PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'material_id'       => $this->semenMaterial->id,
            'quantity'          => 100,
            'received_qty'      => 0,
            'unit_price'        => 65000,
            'total_price'       => 6500000,
        ]);

        // 2. Project admin receives 40 bags (partial)
        $this->actingAs($adminProyek);
        $response = $this->post(route('goods-receipts.store'), [
            'purchase_order_id' => $po->id,
            'supplier_id'       => $this->supplier->id,
            'warehouse_id'      => $this->projectWarehouse->id,
            'received_at'       => now()->toDateString(),
            'invoice_number'    => 'SJ-PARTIAL-01',
            'items'             => [
                [
                    'material_id'            => $this->semenMaterial->id,
                    'purchase_order_item_id' => $poItem->id,
                    'quantity'               => 40,
                    'unit_price'             => 65000,
                ],
            ],
        ]);

        $receipt = \App\Models\GoodsReceipt::where('invoice_number', 'SJ-PARTIAL-01')->firstOrFail();
        $this->assertEquals('draft', $receipt->status);

        // 3. Confirm receipt by Project Admin
        $confirmResponse = $this->post(route('goods-receipts.confirm', $receipt));
        $confirmResponse->assertSessionHas('success');

        // Verify PO status becomes partial_received
        $po->refresh();
        $this->assertEquals('partial_received', $po->status);
        $this->assertEquals(40, $poItem->fresh()->received_qty);

        // Verify Project stock
        $projectStock = Inventory::where('warehouse_id', $this->projectWarehouse->id)
            ->where('material_id', $this->semenMaterial->id)
            ->value('quantity');
        $this->assertGreaterThanOrEqual(40, (float) $projectStock);
    }

    public function test_custom_item_auto_registration_on_goods_receipt(): void
    {
        $adminProyek = User::where('email', 'admin.proyek1@arsikon.co.id')->firstOrFail();

        // 1. Create PO with custom/manual item
        $po = \App\Models\PurchaseOrder::create([
            'po_number'     => 'PO-2026-CUSTOM',
            'supplier_id'   => $this->supplier->id,
            'supplier_name' => $this->supplier->name,
            'created_by'    => $this->adminUser->id,
            'status'        => 'sent',
            'order_date'    => now()->toDateString(),
            'total_amount'  => 5000000,
        ]);

        $poItem = \App\Models\PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'material_id'       => null,
            'custom_item_name'  => 'Granit Motif Khusus 60x60',
            'custom_item_unit'  => 'Dus',
            'quantity'          => 50,
            'received_qty'      => 0,
            'unit_price'        => 100000,
            'total_price'       => 5000000,
        ]);

        // 2. Receive via GoodsReceiptController::store
        $this->actingAs($adminProyek);
        $response = $this->post(route('goods-receipts.store'), [
            'purchase_order_id' => $po->id,
            'supplier_id'       => $this->supplier->id,
            'warehouse_id'      => $this->projectWarehouse->id,
            'received_at'       => now()->toDateString(),
            'invoice_number'    => 'SJ-GRANIT-01',
            'items'             => [
                [
                    'material_id'            => null,
                    'purchase_order_item_id' => $poItem->id,
                    'quantity'               => 50,
                    'unit_price'             => 100000,
                ],
            ],
        ]);

        $receipt = \App\Models\GoodsReceipt::where('invoice_number', 'SJ-GRANIT-01')->firstOrFail();
        
        // Confirm receipt
        $this->post(route('goods-receipts.confirm', $receipt));

        // 3. Verify material is auto-registered
        $newMaterial = Material::where('name', 'Granit Motif Khusus 60x60')->first();
        $this->assertNotNull($newMaterial, 'Material custom harus terdaftar di master data.');
        $this->assertStringStartsWith('MAT-AUTO-', $newMaterial->sku);

        // Verify stock in project warehouse
        $stock = Inventory::where('warehouse_id', $this->projectWarehouse->id)
            ->where('material_id', $newMaterial->id)
            ->value('quantity');
        $this->assertEquals(50, $stock);
    }

    public function test_unauthorized_user_cannot_confirm_goods_receipt_for_another_warehouse(): void
    {
        $adminProyek1 = User::where('email', 'admin.proyek1@arsikon.co.id')->firstOrFail();
        $projectWarehouseB = Warehouse::where('code', 'W-PRJ-002')->firstOrFail();

        // Goods receipt for Project Warehouse B
        $receipt = $this->goodsReceiptService->create([
            'supplier_id'    => $this->supplier->id,
            'warehouse_id'   => $projectWarehouseB->id,
            'received_at'    => now()->toDateString(),
            'invoice_number' => 'SJ-OTHER-WH',
            'items'          => [
                [
                    'material_id' => $this->semenMaterial->id,
                    'quantity'    => 20,
                    'unit_price'  => 65000,
                ],
            ],
        ], $this->adminUser->id);

        // Attempt confirm by adminProyek1 (who is only assigned to Project A)
        $this->actingAs($adminProyek1);
        $response = $this->post(route('goods-receipts.confirm', $receipt));

        $response->assertSessionHas('error');
        $receipt->refresh();
        $this->assertEquals('draft', $receipt->status);
    }
}
