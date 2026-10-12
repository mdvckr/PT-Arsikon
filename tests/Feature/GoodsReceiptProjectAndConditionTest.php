<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\Inventory;
use App\Models\Material;
use App\Models\MaterialRequest;
use App\Models\MaterialRequestItem;
use App\Models\Project;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\Tool;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\GoodsReceiptService;
use Database\Seeders\MasterDataSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoodsReceiptProjectAndConditionTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminPusat;
    protected User $adminProyek;
    protected Supplier $supplier;
    protected Warehouse $centralWarehouse;
    protected Warehouse $projectWarehouse;
    protected Project $project;
    protected Material $material;
    protected Tool $tool;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(MasterDataSeeder::class);

        $this->adminPusat = User::firstOrCreate(
            ['email' => 'admin.pusat.t11@example.com'],
            ['name' => 'Admin Pusat T11', 'password' => bcrypt('password')]
        );
        $this->adminPusat->syncRoles(['Admin Pusat']);

        $this->project = Project::firstOrCreate(
            ['code' => 'PRJ-T11'],
            ['name' => 'Proyek Gedung T11', 'status' => 'active']
        );

        $this->centralWarehouse = Warehouse::where('is_central', true)->first() ?? Warehouse::create([
            'name' => 'Gudang Pusat T11',
            'code' => 'WH-PST-T11',
            'is_central' => true,
        ]);

        $this->projectWarehouse = Warehouse::firstOrCreate(
            ['code' => 'WH-PRY-T11'],
            [
                'name' => 'Gudang Proyek T11',
                'project_id' => $this->project->id,
                'is_central' => false,
                'is_active' => true,
            ]
        );

        $this->adminProyek = User::firstOrCreate(
            ['email' => 'admin.proyek.t11@example.com'],
            [
                'name' => 'Admin Gudang Proyek T11',
                'password' => bcrypt('password'),
            ]
        );
        $this->adminProyek->syncRoles(['Admin Gudang Proyek']);
        $this->adminProyek->warehouses()->syncWithoutDetaching([$this->projectWarehouse->id]);

        $this->supplier = Supplier::firstOrCreate(
            ['name' => 'PT Semen Perkasa T11'],
            ['phone' => '081234567833']
        );

        $categoryMat = Category::where('type', 'material')->first();
        $unit = Unit::first();

        $this->material = Material::firstOrCreate(
            ['sku' => 'MAT-T11-01'],
            [
                'name' => 'Semen T11',
                'category_id' => $categoryMat->id,
                'unit_id' => $unit->id,
            ]
        );

        $categoryTool = Category::where('type', 'tool')->first() ?? Category::create([
            'name' => 'Alat Berat T11',
            'code' => 'CAT-TL-T11',
            'type' => 'tool',
        ]);

        $this->tool = Tool::firstOrCreate(
            ['code' => 'TL-T11-01'],
            [
                'name' => 'Genset 5KVA T11',
                'category_id' => $categoryTool->id,
                'brand' => 'Yamaha',
                'is_active' => true,
            ]
        );
    }

    public function test_tool_receipt_increments_purchase_order_item_received_qty_on_confirm()
    {
        $po = PurchaseOrder::create([
            'supplier_id' => $this->supplier->id,
            'delivery_warehouse_id' => $this->projectWarehouse->id,
            'created_by' => $this->adminPusat->id,
            'status' => 'sent',
            'order_date' => now()->toDateString(),
            'total_amount' => 15000000,
        ]);

        $poItem = PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'tool_id' => $this->tool->id,
            'quantity' => 2,
            'received_qty' => 0,
            'unit_price' => 7500000,
            'subtotal' => 15000000,
        ]);

        $service = app(GoodsReceiptService::class);
        $gr = $service->create([
            'purchase_order_id' => $po->id,
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->projectWarehouse->id,
            'received_at' => now()->toDateString(),
            'items' => [
                [
                    'item_type' => 'tool',
                    'tool_id' => $this->tool->id,
                    'purchase_order_item_id' => $poItem->id,
                    'qty_shipped' => 2,
                    'qty_received' => 2,
                    'qty_damaged' => 0,
                ]
            ],
        ], $this->adminPusat->id);

        $service->confirm($gr, $this->adminPusat->id);

        $poItem->refresh();
        // Bug T11: sebelumnya received_qty pada item alat tidak bertambah
        $this->assertEquals(2, (float) $poItem->received_qty);
        $this->assertEquals(0, (float) $poItem->outstanding);
    }

    public function test_damaged_quantity_does_not_add_to_usable_stock_or_po_received_qty()
    {
        $po = PurchaseOrder::create([
            'supplier_id' => $this->supplier->id,
            'delivery_warehouse_id' => $this->projectWarehouse->id,
            'created_by' => $this->adminPusat->id,
            'status' => 'sent',
            'order_date' => now()->toDateString(),
            'total_amount' => 500000,
        ]);

        $poItem = PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'material_id' => $this->material->id,
            'quantity' => 10,
            'received_qty' => 0,
            'unit_price' => 50000,
            'subtotal' => 500000,
        ]);

        $service = app(GoodsReceiptService::class);
        $gr = $service->create([
            'purchase_order_id' => $po->id,
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->projectWarehouse->id,
            'received_at' => now()->toDateString(),
            'items' => [
                [
                    'item_type' => 'material',
                    'material_id' => $this->material->id,
                    'purchase_order_item_id' => $poItem->id,
                    'qty_shipped' => 10,
                    'qty_received' => 7,  // Diterima BAIK
                    'qty_damaged' => 3,   // RUSAK
                ]
            ],
        ], $this->adminPusat->id);

        $service->confirm($gr, $this->adminPusat->id);

        // 1. Stok di gudang proyek bertambah HANYA 7
        $stock = Inventory::where('warehouse_id', $this->projectWarehouse->id)
            ->where('material_id', $this->material->id)
            ->value('quantity');
        $this->assertEquals(7, (float) $stock);

        // 2. PO item received_qty bertambah HANYA 7
        $poItem->refresh();
        $this->assertEquals(7, (float) $poItem->received_qty);

        // 3. Outstanding PO item masih 3
        $this->assertEquals(3, (float) $poItem->outstanding);

        // 4. Status PO menjadi partial_received
        $po->refresh();
        $this->assertEquals('partial_received', $po->status);
    }

    public function test_goods_receipt_exceeding_po_outstanding_is_rejected()
    {
        $po = PurchaseOrder::create([
            'supplier_id' => $this->supplier->id,
            'delivery_warehouse_id' => $this->projectWarehouse->id,
            'created_by' => $this->adminPusat->id,
            'status' => 'sent',
            'order_date' => now()->toDateString(),
            'total_amount' => 250000,
        ]);

        $poItem = PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'material_id' => $this->material->id,
            'quantity' => 5,
            'received_qty' => 0,
            'unit_price' => 50000,
            'subtotal' => 250000,
        ]);

        $service = app(GoodsReceiptService::class);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('outstanding');

        $service->create([
            'purchase_order_id' => $po->id,
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->projectWarehouse->id,
            'received_at' => now()->toDateString(),
            'items' => [
                [
                    'material_id' => $this->material->id,
                    'purchase_order_item_id' => $poItem->id,
                    'qty_shipped' => 10,
                    'qty_received' => 6, // 6 > outstanding 5
                    'qty_damaged' => 0,
                ]
            ],
        ], $this->adminPusat->id);
    }

    public function test_goods_receipt_calculates_missing_and_sets_unresolved_status()
    {
        $po = PurchaseOrder::create([
            'supplier_id' => $this->supplier->id,
            'delivery_warehouse_id' => $this->projectWarehouse->id,
            'created_by' => $this->adminPusat->id,
            'status' => 'sent',
            'order_date' => now()->toDateString(),
            'total_amount' => 500000,
        ]);

        $poItem = PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'material_id' => $this->material->id,
            'quantity' => 10,
            'received_qty' => 0,
            'unit_price' => 50000,
            'subtotal' => 500000,
        ]);

        $service = app(GoodsReceiptService::class);
        $gr = $service->create([
            'purchase_order_id' => $po->id,
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->projectWarehouse->id,
            'received_at' => now()->toDateString(),
            'items' => [
                [
                    'material_id' => $this->material->id,
                    'purchase_order_item_id' => $poItem->id,
                    'qty_shipped' => 10,
                    'qty_received' => 6,
                    'qty_damaged' => 1,
                ]
            ],
        ], $this->adminPusat->id);

        $grItem = $gr->items()->first();
        // qty_missing = 10 - 6 - 1 = 3
        $this->assertEquals(3, (float) $grItem->qty_missing);
        $this->assertEquals('unresolved', $grItem->missing_status);
    }

    public function test_subsequent_goods_receipt_proportionally_resolves_previous_missing_status()
    {
        $po = PurchaseOrder::create([
            'supplier_id' => $this->supplier->id,
            'delivery_warehouse_id' => $this->projectWarehouse->id,
            'created_by' => $this->adminPusat->id,
            'status' => 'sent',
            'order_date' => now()->toDateString(),
            'total_amount' => 500000,
        ]);

        $poItem = PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'material_id' => $this->material->id,
            'quantity' => 10,
            'received_qty' => 0,
            'unit_price' => 50000,
            'subtotal' => 500000,
        ]);

        $service = app(GoodsReceiptService::class);

        // GR 1: tiba 7 dari 10, hilang 3
        $gr1 = $service->create([
            'purchase_order_id' => $po->id,
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->projectWarehouse->id,
            'received_at' => now()->toDateString(),
            'items' => [
                [
                    'material_id' => $this->material->id,
                    'purchase_order_item_id' => $poItem->id,
                    'qty_shipped' => 10,
                    'qty_received' => 7,
                    'qty_damaged' => 0,
                ]
            ],
        ], $this->adminPusat->id);
        $service->confirm($gr1, $this->adminPusat->id);

        $gr1Item = $gr1->items()->first();
        $this->assertEquals(3, (float) $gr1Item->qty_missing);
        $this->assertEquals('unresolved', $gr1Item->missing_status);

        // GR 2: Vendor mengirimkan sisa 3 barang, diterima penuh
        $gr2 = $service->create([
            'purchase_order_id' => $po->id,
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->projectWarehouse->id,
            'received_at' => now()->toDateString(),
            'items' => [
                [
                    'material_id' => $this->material->id,
                    'purchase_order_item_id' => $poItem->id,
                    'qty_shipped' => 3,
                    'qty_received' => 3,
                    'qty_damaged' => 0,
                ]
            ],
        ], $this->adminPusat->id);
        $service->confirm($gr2, $this->adminPusat->id);

        // Missing status pada GR 1 sebelumnya harus otomatis menjadi resolved
        $this->assertEquals('resolved', $gr1Item->fresh()->missing_status);
    }

    public function test_confirm_gr_updates_mr_item_qty_received_and_recalculates_mr_status()
    {
        $mr = MaterialRequest::create([
            'request_number' => 'MR-2026-T11-01',
            'to_warehouse_id' => $this->projectWarehouse->id,
            'requested_by_user_id' => $this->adminPusat->id,
            'status' => 'approved',
        ]);

        $mrItem = MaterialRequestItem::create([
            'material_request_id' => $mr->id,
            'material_id' => $this->material->id,
            'item_type' => 'material',
            'qty_requested' => 10,
            'qty_approved' => 10,
            'qty_ordered' => 10,
            'qty_received' => 0,
            'qty_issued' => 0,
            'fulfillment' => 'purchase',
        ]);

        $po = PurchaseOrder::create([
            'supplier_id' => $this->supplier->id,
            'delivery_warehouse_id' => $this->projectWarehouse->id,
            'created_by' => $this->adminPusat->id,
            'status' => 'sent',
            'order_date' => now()->toDateString(),
            'total_amount' => 500000,
        ]);

        $poItem = PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'material_id' => $this->material->id,
            'material_request_item_id' => $mrItem->id,
            'quantity' => 10,
            'received_qty' => 0,
            'unit_price' => 50000,
            'subtotal' => 500000,
        ]);

        $service = app(GoodsReceiptService::class);

        // GR 1: Diterima sebagian (6)
        $gr1 = $service->create([
            'purchase_order_id' => $po->id,
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->projectWarehouse->id,
            'received_at' => now()->toDateString(),
            'items' => [
                [
                    'material_id' => $this->material->id,
                    'purchase_order_item_id' => $poItem->id,
                    'qty_shipped' => 6,
                    'qty_received' => 6,
                    'qty_damaged' => 0,
                ]
            ],
        ], $this->adminPusat->id);
        $service->confirm($gr1, $this->adminPusat->id);

        $mrItem->refresh();
        $this->assertEquals(6, (float) $mrItem->qty_received);
        $mr->refresh();
        $this->assertEquals('partially_fulfilled', $mr->status);

        // GR 2: Diterima sisa (4)
        $gr2 = $service->create([
            'purchase_order_id' => $po->id,
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->projectWarehouse->id,
            'received_at' => now()->toDateString(),
            'items' => [
                [
                    'material_id' => $this->material->id,
                    'purchase_order_item_id' => $poItem->id,
                    'qty_shipped' => 4,
                    'qty_received' => 4,
                    'qty_damaged' => 0,
                ]
            ],
        ], $this->adminPusat->id);
        $service->confirm($gr2, $this->adminPusat->id);

        $mrItem->refresh();
        $this->assertEquals(10, (float) $mrItem->qty_received);
        $mr->refresh();
        $this->assertEquals('fulfilled', $mr->status);
    }
}
