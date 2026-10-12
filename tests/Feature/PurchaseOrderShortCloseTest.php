<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Material;
use App\Models\MaterialRequest;
use App\Models\MaterialRequestItem;
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

class PurchaseOrderShortCloseTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminPusat;
    protected User $creatorPO;
    protected User $closerPO;
    protected User $unauthorizedUser;
    protected Supplier $supplier;
    protected Warehouse $warehouse;
    protected Material $material;
    protected MaterialRequest $mr;
    protected MaterialRequestItem $mrItem;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(MasterDataSeeder::class);

        $this->adminPusat = User::firstOrCreate(
            ['email' => 'admin.pusat.t10@example.com'],
            ['name' => 'Admin Pusat T10', 'password' => bcrypt('password')]
        );
        $this->adminPusat->syncRoles(['Admin Pusat']);

        $this->creatorPO = User::firstOrCreate(
            ['email' => 'creator.po.t10@example.com'],
            ['name' => 'Creator PO T10', 'password' => bcrypt('password')]
        );
        $this->creatorPO->syncRoles(['Admin PO']);

        $this->closerPO = User::firstOrCreate(
            ['email' => 'closer.po.t10@example.com'],
            ['name' => 'Closer PO T10', 'password' => bcrypt('password')]
        );
        $this->closerPO->syncRoles(['Admin Pusat']);

        $this->unauthorizedUser = User::firstOrCreate(
            ['email' => 'karyawan.t10@example.com'],
            ['name' => 'Karyawan T10', 'password' => bcrypt('password')]
        );
        $this->unauthorizedUser->syncRoles(['Karyawan']);

        $this->supplier = Supplier::first() ?? Supplier::create([
            'name' => 'PT Supplier T10',
            'phone' => '081234567822',
        ]);

        $this->warehouse = Warehouse::first() ?? Warehouse::create([
            'name' => 'Gudang Pusat T10',
            'code' => 'WH-PST-T10',
            'is_central' => true,
        ]);

        $category = Category::where('type', 'material')->first();
        $unit = Unit::first();

        $this->material = Material::firstOrCreate(
            ['sku' => 'MAT-T10-01'],
            [
                'name' => 'Besi Ulir T10',
                'category_id' => $category->id,
                'unit_id' => $unit->id,
            ]
        );

        $this->mr = MaterialRequest::create([
            'request_number' => 'MR-2026-T1001',
            'to_warehouse_id' => $this->warehouse->id,
            'requested_by_user_id' => $this->adminPusat->id,
            'status' => 'approved',
        ]);

        $this->mrItem = MaterialRequestItem::create([
            'material_request_id' => $this->mr->id,
            'material_id' => $this->material->id,
            'item_type' => 'material',
            'qty_requested' => 10,
            'qty_approved' => 10,
            'qty_ordered' => 10, // Diasumsikan sudah dipesan ke PO
            'qty_received' => 0,
            'qty_issued' => 0,
            'fulfillment' => 'purchase',
        ]);
    }

    public function test_creator_cannot_short_close_own_purchase_order()
    {
        $po = PurchaseOrder::create([
            'supplier_id' => $this->supplier->id,
            'delivery_warehouse_id' => $this->warehouse->id,
            'created_by' => $this->creatorPO->id,
            'status' => 'sent',
            'order_date' => now()->toDateString(),
            'total_amount' => 500000,
        ]);

        $poItem = PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'material_id' => $this->material->id,
            'material_request_item_id' => $this->mrItem->id,
            'quantity' => 10,
            'received_qty' => 4,
            'unit_price' => 50000,
            'subtotal' => 500000,
        ]);

        $this->creatorPO->givePermissionTo('close purchase orders');

        $response = $this->actingAs($this->creatorPO)
            ->post(route('purchase-orders.close', $po), ['reason' => 'Vendor kehabisan stok']);

        $response->assertSessionHas('error');
        $po->refresh();
        $this->assertEquals('sent', $po->status);
        $this->assertEquals(0, (float) $poItem->fresh()->short_closed_qty);
    }

    public function test_unauthorized_user_cannot_short_close_purchase_order()
    {
        $po = PurchaseOrder::create([
            'supplier_id' => $this->supplier->id,
            'delivery_warehouse_id' => $this->warehouse->id,
            'created_by' => $this->creatorPO->id,
            'status' => 'sent',
            'order_date' => now()->toDateString(),
            'total_amount' => 500000,
        ]);

        $response = $this->actingAs($this->unauthorizedUser)
            ->post(route('purchase-orders.close', $po), ['reason' => 'Tutup sisa']);

        $response->assertStatus(403);
    }

    public function test_cannot_short_close_po_in_invalid_status()
    {
        $po = PurchaseOrder::create([
            'supplier_id' => $this->supplier->id,
            'delivery_warehouse_id' => $this->warehouse->id,
            'created_by' => $this->creatorPO->id,
            'status' => 'draft',
            'order_date' => now()->toDateString(),
            'total_amount' => 500000,
        ]);

        $response = $this->actingAs($this->closerPO)
            ->post(route('purchase-orders.close', $po), ['reason' => 'Tutup draft']);

        $response->assertSessionHas('error');
        $po->refresh();
        $this->assertEquals('draft', $po->status);
    }

    public function test_cannot_short_close_po_with_no_outstanding()
    {
        $po = PurchaseOrder::create([
            'supplier_id' => $this->supplier->id,
            'delivery_warehouse_id' => $this->warehouse->id,
            'created_by' => $this->creatorPO->id,
            'status' => 'sent',
            'order_date' => now()->toDateString(),
            'total_amount' => 500000,
        ]);

        PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'material_id' => $this->material->id,
            'quantity' => 10,
            'received_qty' => 10, // Sudah penuh, outstanding = 0
            'unit_price' => 50000,
            'subtotal' => 500000,
        ]);

        $response = $this->actingAs($this->closerPO)
            ->post(route('purchase-orders.close', $po), ['reason' => 'Tutup']);

        $response->assertSessionHas('error');
        $po->refresh();
        $this->assertEquals('sent', $po->status);
    }

    public function test_short_close_requires_reason()
    {
        $po = PurchaseOrder::create([
            'supplier_id' => $this->supplier->id,
            'delivery_warehouse_id' => $this->warehouse->id,
            'created_by' => $this->creatorPO->id,
            'status' => 'sent',
            'order_date' => now()->toDateString(),
            'total_amount' => 500000,
        ]);

        PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'material_id' => $this->material->id,
            'quantity' => 10,
            'received_qty' => 4,
            'unit_price' => 50000,
            'subtotal' => 500000,
        ]);

        $response = $this->actingAs($this->closerPO)
            ->post(route('purchase-orders.close', $po), []);

        $response->assertSessionHasErrors('reason');
    }

    public function test_authorized_user_can_short_close_sent_or_partial_received_po()
    {
        $po = PurchaseOrder::create([
            'supplier_id' => $this->supplier->id,
            'delivery_warehouse_id' => $this->warehouse->id,
            'created_by' => $this->creatorPO->id,
            'status' => 'partial_received',
            'order_date' => now()->toDateString(),
            'total_amount' => 500000,
        ]);

        $poItem = PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'material_id' => $this->material->id,
            'material_request_item_id' => $this->mrItem->id,
            'quantity' => 10,
            'received_qty' => 4,
            'unit_price' => 50000,
            'subtotal' => 500000,
        ]);

        $this->assertEquals(10, (float) $this->mrItem->fresh()->qty_ordered);

        $response = $this->actingAs($this->closerPO)
            ->post(route('purchase-orders.close', $po), [
                'reason' => 'Sisa 6 tidak sanggup dipenuhi supplier, alihkan ke vendor lain',
            ]);

        $response->assertSessionHas('success');

        $po->refresh();
        $this->assertEquals('closed', $po->status);
        $this->assertStringContainsString('Sisa 6 tidak sanggup dipenuhi supplier', $po->notes);

        $poItem->refresh();
        $this->assertEquals(6, (float) $poItem->short_closed_qty);
        $this->assertEquals(0, (float) $poItem->outstanding);

        // mrItem qty_ordered harus berkurang sebesar sisa yang ditutup (10 - 6 = 4)
        $this->mrItem->refresh();
        $this->assertEquals(4, (float) $this->mrItem->qty_ordered);
    }
}
