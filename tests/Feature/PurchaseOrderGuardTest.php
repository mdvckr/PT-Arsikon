<?php

namespace Tests\Feature;

use App\Models\GoodsReceipt;
use App\Models\Material;
use App\Models\MaterialRequest;
use App\Models\MaterialRequestItem;
use App\Models\ProcurementRequest;
use App\Models\ProcurementRequestItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use Database\Seeders\MasterDataSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseOrderGuardTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminPusat;
    protected User $adminPO;
    protected User $karyawan;
    protected Supplier $supplier;
    protected Warehouse $centralWarehouse;
    protected Warehouse $projectWarehouse;
    protected Material $material;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(MasterDataSeeder::class);

        $this->adminPusat = User::firstOrCreate(
            ['email' => 'admin.guard@example.com'],
            ['name' => 'Admin Guard', 'password' => bcrypt('password')]
        );
        $this->adminPusat->syncRoles(['Admin Pusat']);

        $this->adminPO = User::firstOrCreate(
            ['email' => 'po.guard@example.com'],
            ['name' => 'PO Guard', 'password' => bcrypt('password')]
        );
        $this->adminPO->syncRoles(['Admin PO']);

        $this->karyawan = User::firstOrCreate(
            ['email' => 'karyawan.guard@example.com'],
            ['name' => 'Karyawan Guard', 'password' => bcrypt('password')]
        );
        $this->karyawan->syncRoles(['Karyawan']);

        $this->supplier = Supplier::first() ?? Supplier::create([
            'name' => 'PT Mitra Supplier',
            'phone' => '081234567890',
        ]);

        $this->centralWarehouse = Warehouse::where('is_central', true)->first() ?? Warehouse::create([
            'name' => 'Gudang Pusat Test',
            'code' => 'WH-PST-TEST',
            'is_central' => true,
        ]);

        $this->projectWarehouse = Warehouse::where('is_central', false)->first() ?? Warehouse::create([
            'name' => 'Gudang Proyek Test',
            'code' => 'WH-PRY-TEST',
            'is_central' => false,
        ]);

        $category = \App\Models\Category::where('type', 'material')->first();
        $unit = \App\Models\Unit::first();
        $this->material = Material::firstOrCreate(
            ['sku' => 'MAT-SMN'],
            [
                'name' => 'Semen Portland',
                'category_id' => $category->id,
                'unit_id' => $unit->id,
                'min_stock_central' => 10,
            ]
        );
    }

    public function test_send_po_fails_when_po_is_in_draft_status(): void
    {
        $po = PurchaseOrder::create([
            'po_number' => 'PO-TEST-001',
            'supplier_id' => $this->supplier->id,
            'created_by' => $this->adminPO->id,
            'status' => 'draft',
            'order_date' => now()->toDateString(),
            'total_amount' => 100000,
        ]);

        $response = $this->actingAs($this->adminPusat)
            ->post(route('purchase-orders.send', $po));

        $response->assertSessionHas('error');
        $this->assertEquals('draft', $po->fresh()->status);
    }

    public function test_send_po_succeeds_when_po_is_approved(): void
    {
        $po = PurchaseOrder::create([
            'po_number' => 'PO-TEST-002',
            'supplier_id' => $this->supplier->id,
            'created_by' => $this->adminPO->id,
            'approved_by' => $this->adminPusat->id,
            'approved_at' => now(),
            'status' => 'approved',
            'order_date' => now()->toDateString(),
            'total_amount' => 100000,
        ]);

        $response = $this->actingAs($this->adminPusat)
            ->post(route('purchase-orders.send', $po));

        $response->assertSessionHas('success');
        $this->assertEquals('sent', $po->fresh()->status);
    }

    public function test_unauthorized_user_cannot_send_or_cancel_po(): void
    {
        $po = PurchaseOrder::create([
            'po_number' => 'PO-TEST-003',
            'supplier_id' => $this->supplier->id,
            'created_by' => $this->adminPO->id,
            'status' => 'approved',
            'order_date' => now()->toDateString(),
            'total_amount' => 100000,
        ]);

        $this->actingAs($this->karyawan)
            ->post(route('purchase-orders.send', $po))
            ->assertStatus(403);

        $this->actingAs($this->karyawan)
            ->post(route('purchase-orders.cancel', $po))
            ->assertStatus(403);
    }

    public function test_cancel_po_fails_if_goods_receipt_is_confirmed(): void
    {
        $po = PurchaseOrder::create([
            'po_number' => 'PO-TEST-004',
            'supplier_id' => $this->supplier->id,
            'created_by' => $this->adminPO->id,
            'status' => 'sent',
            'order_date' => now()->toDateString(),
            'total_amount' => 100000,
        ]);

        // Create confirmed Goods Receipt
        GoodsReceipt::create([
            'receipt_number' => 'GR-TEST-001',
            'purchase_order_id' => $po->id,
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->centralWarehouse->id,
            'received_by_user_id' => $this->adminPusat->id,
            'created_by' => $this->adminPusat->id,
            'status' => 'confirmed',
            'receipt_date' => now()->toDateString(),
            'confirmed_by' => $this->adminPusat->id,
            'confirmed_at' => now(),
        ]);

        $response = $this->actingAs($this->adminPusat)
            ->post(route('purchase-orders.cancel', $po));

        $response->assertSessionHas('error');
        $this->assertEquals('sent', $po->fresh()->status);
    }

    public function test_cancel_po_releases_ordered_quantities_on_pr_and_recalculates_status(): void
    {
        $pr = ProcurementRequest::create([
            'pr_number' => 'PR-TEST-001',
            'requested_by' => $this->adminPusat->id,
            'status' => 'fully_ordered',
            'needed_by' => now()->addDays(7)->toDateString(),
        ]);

        $prItem = ProcurementRequestItem::create([
            'procurement_request_id' => $pr->id,
            'material_id' => $this->material->id,
            'quantity' => 50,
            'qty_ordered' => 50,
            'estimated_price' => 10000,
        ]);

        $po = PurchaseOrder::create([
            'po_number' => 'PO-TEST-005',
            'procurement_request_id' => $pr->id,
            'supplier_id' => $this->supplier->id,
            'created_by' => $this->adminPO->id,
            'status' => 'approved',
            'order_date' => now()->toDateString(),
            'total_amount' => 500000,
        ]);

        $poItem = PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'material_id' => $this->material->id,
            'procurement_request_item_id' => $prItem->id,
            'quantity' => 50,
            'unit_price' => 10000,
            'subtotal' => 500000,
        ]);

        $response = $this->actingAs($this->adminPusat)
            ->post(route('purchase-orders.cancel', $po));

        $response->assertSessionHas('success');
        $this->assertEquals('cancelled', $po->fresh()->status);

        // PR item qty_ordered berkurang kembali menjadi 0
        $this->assertEquals(0, (float) $prItem->fresh()->qty_ordered);

        // PR status kembali menjadi approved karena kuota kembali
        $this->assertEquals('approved', $pr->fresh()->status);
    }

    public function test_cancel_po_releases_ordered_quantities_on_mr(): void
    {
        $mr = MaterialRequest::create([
            'request_number' => 'MR-TEST-001',
            'from_warehouse_id' => $this->centralWarehouse->id,
            'to_warehouse_id' => $this->projectWarehouse->id,
            'requested_by_user_id' => $this->adminPusat->id,
            'status' => 'approved',
        ]);

        $mrItem = MaterialRequestItem::create([
            'material_request_id' => $mr->id,
            'material_id' => $this->material->id,
            'qty_requested' => 10,
            'qty_approved' => 10,
            'qty_ordered' => 10,
        ]);

        $po = PurchaseOrder::create([
            'po_number' => 'PO-TEST-006',
            'supplier_id' => $this->supplier->id,
            'created_by' => $this->adminPO->id,
            'status' => 'approved',
            'order_date' => now()->toDateString(),
            'total_amount' => 100000,
        ]);

        $poItem = PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'material_id' => $this->material->id,
            'material_request_item_id' => $mrItem->id,
            'quantity' => 10,
            'unit_price' => 10000,
            'subtotal' => 100000,
        ]);

        $response = $this->actingAs($this->adminPusat)
            ->post(route('purchase-orders.cancel', $po));

        $response->assertSessionHas('success');
        $this->assertEquals('cancelled', $po->fresh()->status);
        $this->assertEquals(0, (float) $mrItem->fresh()->qty_ordered);
    }
}
