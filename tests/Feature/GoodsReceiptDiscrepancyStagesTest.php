<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\Material;
use App\Models\Project;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\GoodsReceiptService;
use Database\Seeders\MasterDataSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoodsReceiptDiscrepancyStagesTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminPusat;
    protected Supplier $supplier;
    protected Warehouse $warehouse;
    protected Material $material;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(MasterDataSeeder::class);

        $this->adminPusat = User::firstOrCreate(
            ['email' => 'admin.discrepancy@example.com'],
            ['name' => 'Admin Discrepancy Test', 'password' => bcrypt('password')]
        );
        $this->adminPusat->syncRoles(['Admin Pusat']);

        $this->supplier = Supplier::firstOrCreate(
            ['name' => 'Supplier Jaya Test'],
            ['address' => 'Jl. Test', 'phone' => '08123456789', 'is_active' => true]
        );

        $this->warehouse = Warehouse::where('is_central', true)->first() ?? Warehouse::create([
            'name' => 'Gudang Pusat Discrepancy',
            'code' => 'WH-PST-DSC',
            'is_central' => true,
            'is_active' => true,
        ]);

        $category = Category::firstOrCreate(['name' => 'Material Konstruksi'], ['code' => 'CAT-MAT-DSC', 'type' => 'material']);
        $unit = Unit::firstOrCreate(['code' => 'pcs'], ['name' => 'Pcs']);

        $this->material = Material::firstOrCreate(
            ['sku' => 'BAT-DSC'],
            [
                'name' => 'Batu Alam Test',
                'sku' => 'BAT-DSC',
                'category_id' => $category->id,
                'unit_id' => $unit->id,
                'is_active' => true,
            ]
        );
    }

    public function test_goods_receipt_with_lost_discrepancy_closes_po_and_clears_incoming_plan(): void
    {
        $po = PurchaseOrder::create([
            'po_number' => 'PO-DSC-0001',
            'supplier_id' => $this->supplier->id,
            'delivery_warehouse_id' => $this->warehouse->id,
            'status' => 'sent',
            'order_date' => now()->toDateString(),
            'total_amount' => 200000,
            'created_by' => $this->adminPusat->id,
        ]);

        $poItem = PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'material_id' => $this->material->id,
            'quantity' => 20,
            'received_qty' => 0,
            'lost_qty' => 0,
            'unit_price' => 10000,
            'subtotal' => 200000,
        ]);

        $service = app(GoodsReceiptService::class);
        $gr = $service->create([
            'purchase_order_id' => $po->id,
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'received_at' => now()->toDateString(),
            'items' => [
                [
                    'material_id' => $this->material->id,
                    'purchase_order_item_id' => $poItem->id,
                    'qty_shipped' => 20,
                    'qty_received' => 10,
                    'qty_missing' => 10,
                    'missing_action' => 'lost',
                    'notes' => '10 pcs hilang / rusak di perjalanan',
                ]
            ]
        ], $this->adminPusat->id);

        $service->confirm($gr, $this->adminPusat->id);

        $poItem->refresh();
        $this->assertEquals(10, (float) $poItem->received_qty);
        $this->assertEquals(10, (float) $poItem->lost_qty);
        $this->assertEquals(0, (float) $poItem->outstanding);
        $this->assertEquals('received', $po->fresh()->status);

        $grItem = $gr->items()->first();
        $this->assertEquals(10, (float) $grItem->qty_missing);
        $this->assertEquals('confirmed_lost', $grItem->missing_status);

        // Stages for Material
        $stages = $this->material->fresh()->getDynamicIncomingStages();
        $this->assertCount(1, $stages);
        $firstStage = $stages->first();
        $this->assertEquals(10, (float) $firstStage['received_qty']);
        $this->assertEquals(0, (float) $firstStage['remaining_qty']);
        $this->assertEquals('received', $firstStage['status']);
        $this->assertEquals(10, (float) ($firstStage['lost_qty'] ?? 0));
    }

    public function test_goods_receipt_with_pending_discrepancy_keeps_po_partial(): void
    {
        $po = PurchaseOrder::create([
            'po_number' => 'PO-DSC-0002',
            'supplier_id' => $this->supplier->id,
            'delivery_warehouse_id' => $this->warehouse->id,
            'status' => 'sent',
            'order_date' => now()->toDateString(),
            'total_amount' => 200000,
            'created_by' => $this->adminPusat->id,
        ]);

        $poItem = PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'material_id' => $this->material->id,
            'quantity' => 20,
            'received_qty' => 0,
            'lost_qty' => 0,
            'unit_price' => 10000,
            'subtotal' => 200000,
        ]);

        $service = app(GoodsReceiptService::class);
        $gr = $service->create([
            'purchase_order_id' => $po->id,
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'received_at' => now()->toDateString(),
            'items' => [
                [
                    'material_id' => $this->material->id,
                    'purchase_order_item_id' => $poItem->id,
                    'qty_shipped' => 20,
                    'qty_received' => 10,
                    'qty_missing' => 10,
                    'missing_action' => 'pending',
                    'notes' => 'Sisa 10 akan dikirim minggu depan',
                ]
            ]
        ], $this->adminPusat->id);

        $service->confirm($gr, $this->adminPusat->id);

        $poItem->refresh();
        $this->assertEquals(10, (float) $poItem->received_qty);
        $this->assertEquals(0, (float) $poItem->lost_qty);
        $this->assertEquals(10, (float) $poItem->outstanding);
        $this->assertEquals('partial_received', $po->fresh()->status);

        $grItem = $gr->items()->first();
        $this->assertEquals(10, (float) $grItem->qty_missing);
        $this->assertEquals('unresolved', $grItem->missing_status);

        // Stages for Material
        $stages = $this->material->fresh()->getDynamicIncomingStages();
        $this->assertCount(1, $stages);
        $firstStage = $stages->first();
        $this->assertEquals(10, (float) $firstStage['received_qty']);
        $this->assertEquals(10, (float) $firstStage['remaining_qty']);
        $this->assertEquals('partial', $firstStage['status']);
    }
}
