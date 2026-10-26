<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\GoodsReceipt;
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

class PurchaseOrderEditTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminPusat;
    protected User $adminPO;
    protected User $poNotaHarian;
    protected Supplier $supplierA;
    protected Supplier $supplierB;
    protected Warehouse $warehouse;
    protected Material $materialA;
    protected Material $materialB;
    protected MaterialRequest $mr;
    protected MaterialRequestItem $mrItemA;
    protected MaterialRequestItem $mrItemB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(MasterDataSeeder::class);

        $this->adminPusat = User::firstOrCreate(
            ['email' => 'admin.pusat.poedit@example.com'],
            ['name' => 'Admin Pusat PO Edit', 'password' => bcrypt('password')]
        );
        $this->adminPusat->syncRoles(['Admin Pusat']);

        $this->adminPO = User::firstOrCreate(
            ['email' => 'admin.po.poedit@example.com'],
            ['name' => 'Admin PO Edit', 'password' => bcrypt('password')]
        );
        $this->adminPO->syncRoles(['Admin PO']);

        $this->poNotaHarian = User::firstOrCreate(
            ['email' => 'po.nota.poedit@example.com'],
            ['name' => 'PO Nota Harian Read Only', 'password' => bcrypt('password')]
        );
        $this->poNotaHarian->syncRoles(['admin-po-nota']);

        $this->supplierA = Supplier::create(['name' => 'Supplier Alpha', 'phone' => '081234567891']);
        $this->supplierB = Supplier::create(['name' => 'Supplier Beta', 'phone' => '081234567892']);

        $this->warehouse = Warehouse::create([
            'name' => 'Gudang Pusat Test',
            'code' => 'WH-PST-TEST',
            'is_central' => true,
            'is_active' => true,
        ]);

        $category = Category::where('type', 'material')->first();
        $unit = Unit::first();

        $this->materialA = Material::create([
            'sku' => 'MAT-TEST-A',
            'name' => 'Semen Padang 50kg',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
        ]);

        $this->materialB = Material::create([
            'sku' => 'MAT-TEST-B',
            'name' => 'Pasir Cor 1 Truk',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
        ]);

        $this->mr = MaterialRequest::create([
            'request_number' => 'MR-TEST-001',
            'from_warehouse_id' => $this->warehouse->id,
            'to_warehouse_id' => $this->warehouse->id,
            'requested_by_user_id' => $this->adminPusat->id,
            'status' => 'approved',
        ]);

        $this->mrItemA = MaterialRequestItem::create([
            'material_request_id' => $this->mr->id,
            'material_id' => $this->materialA->id,
            'qty_requested' => 100,
            'qty_approved' => 100,
            'qty_ordered' => 20,
            'fulfillment' => 'purchase',
        ]);

        $this->mrItemB = MaterialRequestItem::create([
            'material_request_id' => $this->mr->id,
            'material_id' => $this->materialB->id,
            'qty_requested' => 10,
            'qty_approved' => 10,
            'qty_ordered' => 0,
            'fulfillment' => 'purchase',
        ]);
    }

    public function test_admin_can_access_edit_purchase_order_page()
    {
        $po = PurchaseOrder::create([
            'supplier_id' => $this->supplierA->id,
            'delivery_warehouse_id' => $this->warehouse->id,
            'created_by' => $this->adminPO->id,
            'status' => 'draft',
            'order_date' => now()->toDateString(),
            'total_amount' => 1400000,
        ]);

        PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'material_id' => $this->materialA->id,
            'material_request_item_id' => $this->mrItemA->id,
            'quantity' => 20,
            'unit_price' => 70000,
            'subtotal' => 1400000,
        ]);

        $response = $this->actingAs($this->adminPO)->get(route('purchase-orders.edit', $po));
        $response->assertStatus(200);
        $response->assertSee('Edit Purchase Order');
        $response->assertSee($po->po_number);
        $response->assertSee('Supplier Alpha');
        $response->assertSee('Semen Padang 50kg');
    }

    public function test_read_only_role_cannot_edit_purchase_order()
    {
        $po = PurchaseOrder::create([
            'supplier_id' => $this->supplierA->id,
            'delivery_warehouse_id' => $this->warehouse->id,
            'created_by' => $this->adminPusat->id,
            'status' => 'draft',
            'order_date' => now()->toDateString(),
            'total_amount' => 1400000,
        ]);

        $response = $this->actingAs($this->poNotaHarian)->get(route('purchase-orders.edit', $po));
        $response->assertStatus(403);
    }

    public function test_cannot_edit_purchase_order_with_confirmed_goods_receipt()
    {
        $po = PurchaseOrder::create([
            'supplier_id' => $this->supplierA->id,
            'delivery_warehouse_id' => $this->warehouse->id,
            'created_by' => $this->adminPO->id,
            'status' => 'partial_received',
            'order_date' => now()->toDateString(),
            'total_amount' => 1400000,
        ]);

        GoodsReceipt::create([
            'receipt_number' => 'GR-TEST-001',
            'purchase_order_id' => $po->id,
            'supplier_id' => $this->supplierA->id,
            'warehouse_id' => $this->warehouse->id,
            'received_by_user_id' => $this->adminPusat->id,
            'receipt_date' => now()->toDateString(),
            'status' => 'confirmed',
        ]);

        $response = $this->actingAs($this->adminPO)->get(route('purchase-orders.edit', $po));
        $response->assertRedirect(route('purchase-orders.show', $po));
        $response->assertSessionHas('error');
    }

    public function test_purchase_order_can_be_comprehensively_updated()
    {
        $po = PurchaseOrder::create([
            'supplier_id' => $this->supplierA->id,
            'delivery_warehouse_id' => $this->warehouse->id,
            'material_request_id' => $this->mr->id,
            'created_by' => $this->adminPO->id,
            'status' => 'draft',
            'order_date' => now()->toDateString(),
            'total_amount' => 1400000,
            'contact_person' => 'Bpk. Lama',
            'notes' => 'Catatan lama',
        ]);

        $poItem = PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'material_id' => $this->materialA->id,
            'material_request_item_id' => $this->mrItemA->id,
            'quantity' => 20,
            'unit_price' => 70000,
            'subtotal' => 1400000,
        ]);

        $this->assertEquals(20, (float)$this->mrItemA->fresh()->qty_ordered);

        // Edit secara menyeluruh: ganti supplier, ubah item A kuantitas jadi 30, harga jadi 75000, dan tambah item B sebanyak 2
        $updatePayload = [
            'supplier_id' => $this->supplierB->id,
            'supplier_name' => '',
            'contact_person' => 'Bpk. Baru',
            'material_request_id' => $this->mr->id,
            'delivery_warehouse_id' => $this->warehouse->id,
            'risk_bearer' => 'buyer',
            'order_date' => now()->toDateString(),
            'expected_delivery' => now()->addDays(5)->toDateString(),
            'terms' => 'Net 14 Hari',
            'notes' => 'Catatan baru hasil revisi',
            'items' => [
                [
                    'material_id' => $this->materialA->id,
                    'material_request_item_id' => $this->mrItemA->id,
                    'quantity' => 30,
                    'unit_price' => 75000,
                    'notes' => 'Spesifikasi semen revisi',
                ],
                [
                    'material_id' => $this->materialB->id,
                    'material_request_item_id' => $this->mrItemB->id,
                    'quantity' => 2,
                    'unit_price' => 1500000,
                    'notes' => 'Pasir cor tambahan',
                ],
            ],
        ];

        $response = $this->actingAs($this->adminPO)->put(route('purchase-orders.update', $po), $updatePayload);
        $response->assertRedirect(route('purchase-orders.show', $po));
        $response->assertSessionHas('success');

        $po->refresh();
        $this->assertEquals($this->supplierB->id, $po->supplier_id);
        $this->assertEquals('Bpk. Baru', $po->contact_person);
        $this->assertEquals('buyer', $po->risk_bearer);
        $this->assertEquals('Net 14 Hari', $po->terms);
        $this->assertEquals('Catatan baru hasil revisi', $po->notes);

        // Total nilai PO baru: (30 * 75000 = 2.250.000) + (2 * 1.500.000 = 3.000.000) = 5.250.000
        $this->assertEquals(5250000, (float)$po->total_amount);
        $this->assertCount(2, $po->items);

        // Kuota pada item MR tersinkronisasi dengan benar
        $this->assertEquals(30, (float)$this->mrItemA->fresh()->qty_ordered);
        $this->assertEquals(2, (float)$this->mrItemB->fresh()->qty_ordered);
    }

    public function test_updating_approved_po_resets_status_to_draft_for_reapproval()
    {
        $po = PurchaseOrder::create([
            'supplier_id' => $this->supplierA->id,
            'delivery_warehouse_id' => $this->warehouse->id,
            'material_request_id' => $this->mr->id,
            'created_by' => $this->adminPO->id,
            'approved_by' => $this->adminPusat->id,
            'approved_at' => now(),
            'status' => 'approved',
            'order_date' => now()->toDateString(),
            'total_amount' => 1400000,
        ]);

        PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'material_id' => $this->materialA->id,
            'material_request_item_id' => $this->mrItemA->id,
            'quantity' => 20,
            'unit_price' => 70000,
            'subtotal' => 1400000,
        ]);

        $updatePayload = [
            'supplier_id' => $this->supplierA->id,
            'delivery_warehouse_id' => $this->warehouse->id,
            'material_request_id' => $this->mr->id,
            'order_date' => now()->toDateString(),
            'items' => [
                [
                    'material_id' => $this->materialA->id,
                    'material_request_item_id' => $this->mrItemA->id,
                    'quantity' => 25,
                    'unit_price' => 70000,
                ],
            ],
        ];

        $response = $this->actingAs($this->adminPO)->put(route('purchase-orders.update', $po), $updatePayload);
        $response->assertRedirect(route('purchase-orders.show', $po));

        $po->refresh();
        $this->assertEquals('draft', $po->status);
        $this->assertNull($po->approved_by);
        $this->assertNull($po->approved_at);
        $this->assertEquals(25 * 70000, (float)$po->total_amount);
        $this->assertEquals(25, (float)$this->mrItemA->fresh()->qty_ordered);
    }
}
