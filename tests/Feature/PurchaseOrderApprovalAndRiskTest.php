<?php

namespace Tests\Feature;

use App\Models\Material;
use App\Models\MaterialRequest;
use App\Models\MaterialRequestItem;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use Database\Seeders\MasterDataSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseOrderApprovalAndRiskTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminPusat;
    protected User $creatorPO;
    protected User $approverPO;
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
            ['email' => 'admin.pusat.t8@example.com'],
            ['name' => 'Admin Pusat T8', 'password' => bcrypt('password')]
        );
        $this->adminPusat->syncRoles(['Admin Pusat']);

        $this->creatorPO = User::firstOrCreate(
            ['email' => 'creator.po@example.com'],
            ['name' => 'Creator PO', 'password' => bcrypt('password')]
        );
        $this->creatorPO->syncRoles(['Admin PO']);

        $this->approverPO = User::firstOrCreate(
            ['email' => 'approver.po@example.com'],
            ['name' => 'Approver PO', 'password' => bcrypt('password')]
        );
        $this->approverPO->syncRoles(['Admin Pusat']);

        $this->unauthorizedUser = User::firstOrCreate(
            ['email' => 'karyawan.t8@example.com'],
            ['name' => 'Karyawan Biasa', 'password' => bcrypt('password')]
        );
        $this->unauthorizedUser->syncRoles(['Karyawan']);

        $this->supplier = Supplier::first() ?? Supplier::create([
            'name' => 'PT Mitra Pengadaan T8',
            'phone' => '081234567899',
        ]);

        $this->warehouse = Warehouse::first() ?? Warehouse::create([
            'name' => 'Gudang Pusat T8',
            'code' => 'WH-PST-T8',
            'is_central' => true,
        ]);

        $category = \App\Models\Category::where('type', 'material')->first();
        $unit = \App\Models\Unit::first();

        $this->material = Material::firstOrCreate(
            ['sku' => 'MAT-T8-01'],
            [
                'name' => 'Semen Gresik 40kg T8',
                'category_id' => $category->id,
                'unit_id' => $unit->id,
            ]
        );

        // Siapkan MR yang approved dengan fulfillment purchase
        $this->mr = MaterialRequest::create([
            'request_number' => 'MR-2026-T801',
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
            'qty_ordered' => 0,
            'qty_received' => 0,
            'qty_issued' => 0,
            'fulfillment' => 'purchase',
        ]);
    }

    public function test_creator_cannot_approve_their_own_purchase_order()
    {
        $po = PurchaseOrder::create([
            'supplier_id' => $this->supplier->id,
            'supplier_name' => $this->supplier->name,
            'delivery_warehouse_id' => $this->warehouse->id,
            'risk_bearer' => 'vendor',
            'created_by' => $this->creatorPO->id,
            'status' => 'draft',
            'order_date' => now()->toDateString(),
            'total_amount' => 500000,
        ]);

        $this->creatorPO->givePermissionTo('approve purchase orders');

        $response = $this->actingAs($this->creatorPO)
            ->post(route('purchase-orders.approve', $po));

        $response->assertSessionHas('error');
        $po->refresh();
        $this->assertEquals('draft', $po->status);
        $this->assertNull($po->approved_by);
        $this->assertNull($po->approved_at);
    }

    public function test_non_draft_purchase_order_cannot_be_approved()
    {
        $po = PurchaseOrder::create([
            'supplier_id' => $this->supplier->id,
            'supplier_name' => $this->supplier->name,
            'delivery_warehouse_id' => $this->warehouse->id,
            'risk_bearer' => 'vendor',
            'created_by' => $this->creatorPO->id,
            'status' => 'sent',
            'order_date' => now()->toDateString(),
            'total_amount' => 500000,
        ]);

        $response = $this->actingAs($this->approverPO)
            ->post(route('purchase-orders.approve', $po));

        $response->assertSessionHas('error');
        $po->refresh();
        $this->assertEquals('sent', $po->status);
    }

    public function test_user_without_approve_permission_or_role_cannot_approve()
    {
        $po = PurchaseOrder::create([
            'supplier_id' => $this->supplier->id,
            'supplier_name' => $this->supplier->name,
            'delivery_warehouse_id' => $this->warehouse->id,
            'risk_bearer' => 'vendor',
            'created_by' => $this->creatorPO->id,
            'status' => 'draft',
            'order_date' => now()->toDateString(),
            'total_amount' => 500000,
        ]);

        $response = $this->actingAs($this->unauthorizedUser)
            ->post(route('purchase-orders.approve', $po));

        $response->assertStatus(403);
        $po->refresh();
        $this->assertEquals('draft', $po->status);
    }

    public function test_authorized_approver_can_approve_draft_purchase_order()
    {
        $po = PurchaseOrder::create([
            'supplier_id' => $this->supplier->id,
            'supplier_name' => $this->supplier->name,
            'delivery_warehouse_id' => $this->warehouse->id,
            'risk_bearer' => 'vendor',
            'created_by' => $this->creatorPO->id,
            'status' => 'draft',
            'order_date' => now()->toDateString(),
            'total_amount' => 500000,
        ]);

        $response = $this->actingAs($this->approverPO)
            ->post(route('purchase-orders.approve', $po));

        $response->assertSessionHas('success');
        $po->refresh();
        $this->assertEquals('approved', $po->status);
        $this->assertEquals($this->approverPO->id, $po->approved_by);
        $this->assertNotNull($po->approved_at);
    }

    public function test_store_purchase_order_saves_risk_bearer_correctly()
    {
        // 1. Simpan dengan risk_bearer buyer
        $payloadBuyer = [
            'supplier_id' => $this->supplier->id,
            'delivery_warehouse_id' => $this->warehouse->id,
            'risk_bearer' => 'buyer',
            'order_date' => now()->toDateString(),
            'items' => [
                [
                    'material_id' => $this->material->id,
                    'material_request_item_id' => $this->mrItem->id,
                    'quantity' => 4,
                    'unit_price' => 50000,
                ]
            ],
        ];

        $response = $this->actingAs($this->creatorPO)
            ->post(route('purchase-orders.store'), $payloadBuyer);

        $response->assertRedirect();
        $poBuyer = PurchaseOrder::latest('id')->first();
        $this->assertEquals('buyer', $poBuyer->risk_bearer);

        // Cek halaman detail PO menampilkan label pembeli
        $showResponse = $this->actingAs($this->creatorPO)
            ->get(route('purchase-orders.show', $poBuyer));
        $showResponse->assertOk();
        $showResponse->assertSee('Pembeli (PT Arsikon)');

        // 2. Simpan dengan default (vendor)
        $payloadVendor = [
            'supplier_id' => $this->supplier->id,
            'delivery_warehouse_id' => $this->warehouse->id,
            'risk_bearer' => 'vendor',
            'order_date' => now()->toDateString(),
            'items' => [
                [
                    'material_id' => $this->material->id,
                    'material_request_item_id' => $this->mrItem->id,
                    'quantity' => 3,
                    'unit_price' => 50000,
                ]
            ],
        ];

        $response2 = $this->actingAs($this->creatorPO)
            ->post(route('purchase-orders.store'), $payloadVendor);

        $response2->assertRedirect();
        $poVendor = PurchaseOrder::latest('id')->first();
        $this->assertEquals('vendor', $poVendor->risk_bearer);

        $showResponse2 = $this->actingAs($this->creatorPO)
            ->get(route('purchase-orders.show', $poVendor));
        $showResponse2->assertOk();
        $showResponse2->assertSee('Vendor / Supplier');
    }
}
