<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\Inventory;
use App\Models\Material;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Database\Seeders\MasterDataSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IncomingStagesVisibilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(MasterDataSeeder::class);
    }

    public function test_incoming_stages_displays_received_po_and_goods_receipt(): void
    {
        $warehouse = Warehouse::where('is_central', false)->first() ?? Warehouse::create([
            'name'       => 'Gudang Proyek Test',
            'code'       => 'WH-PRY-STG-TEST',
            'is_central' => false,
        ]);

        $admin = User::firstOrCreate(
            ['email' => 'admin.gudang.stage.test@example.com'],
            ['name' => 'Admin Gudang Test', 'password' => bcrypt('password')]
        );
        $admin->syncRoles(['Admin Gudang Proyek']);
        $admin->warehouses()->syncWithoutDetaching([$warehouse->id]);

        $supplier = Supplier::firstOrCreate(
            ['code' => 'SUPP-STAGE-01'],
            ['name' => 'Supplier ABC', 'address' => 'Jl. Test']
        );

        $category = Category::where('type', 'material')->first();
        $unit = Unit::first();

        $material = Material::create([
            'category_id' => $category->id,
            'unit_id'     => $unit->id,
            'sku'         => 'MAT-STG-001',
            'name'        => 'Besi Kencleng Test',
            'is_active'   => true,
        ]);

        Inventory::create([
            'warehouse_id' => $warehouse->id,
            'material_id'  => $material->id,
            'quantity'     => 25,
            'min_stock'    => 5,
        ]);

        // PO with received status
        $po = PurchaseOrder::create([
            'po_number'             => 'PO-TEST-001',
            'supplier_id'           => $supplier->id,
            'supplier_name'         => $supplier->name,
            'delivery_warehouse_id' => $warehouse->id,
            'status'                => 'received',
            'order_date'            => now(),
            'created_by'            => $admin->id,
        ]);

        $poItem = PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'material_id'       => $material->id,
            'quantity'          => 25,
            'received_qty'      => 25,
            'unit_price'        => 50000,
            'subtotal'          => 1250000,
            'total_price'       => 1250000,
        ]);

        // Goods Receipt confirmed
        $gr = GoodsReceipt::create([
            'receipt_number'      => 'GR-TEST-001',
            'purchase_order_id'   => $po->id,
            'supplier_id'         => $supplier->id,
            'warehouse_id'        => $warehouse->id,
            'received_by_user_id' => $admin->id,
            'created_by'          => $admin->id,
            'receipt_date'        => now(),
            'status'              => 'confirmed',
        ]);

        GoodsReceiptItem::create([
            'goods_receipt_id'       => $gr->id,
            'item_type'              => 'material',
            'purchase_order_item_id' => $poItem->id,
            'material_id'            => $material->id,
            'qty_received'           => 25,
            'condition'              => 'good',
        ]);

        // Test model method getDynamicIncomingStages
        $stages = $material->getDynamicIncomingStages($warehouse->id);

        $this->assertCount(1, $stages);
        $stage = $stages->first();

        $this->assertEquals('received', $stage['status']);
        $this->assertEquals('Lengkap Masuk', $stage['status_label']);
        $this->assertEquals(25.0, (float)$stage['qty']);
        $this->assertEquals(25.0, (float)$stage['received_qty']);
        $this->assertEquals(0.0, (float)$stage['remaining_qty']);
        $this->assertStringContainsString('PO-TEST-001', $stage['reference']);
        $this->assertStringContainsString('GR-TEST-001', $stage['reference']);

        // Test view rendering
        $response = $this->actingAs($admin)->get(route('materials.index', ['warehouse_id' => $warehouse->id]));
        $response->assertStatus(200);
        $response->assertSee('PO-TEST-001');
        $response->assertSee('GR-TEST-001');
        $response->assertSee('25');
        $response->assertSee(now()->format('d/m/Y'));
    }
}
