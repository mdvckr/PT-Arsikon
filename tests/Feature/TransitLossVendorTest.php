<?php

namespace Tests\Feature;

use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\Inventory;
use App\Models\Material;
use App\Models\MaterialRequest;
use App\Models\MaterialRequestItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\TransitLoss;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TransitLossVendorTest extends TestCase
{
    use RefreshDatabase;

    protected Warehouse $projectWarehouse;
    protected Warehouse $centralWarehouse;
    protected Supplier $supplier;
    protected User $adminPusat;
    protected User $adminProyek;
    protected User $adminPO;
    protected User $karyawan;
    protected Material $material;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $this->centralWarehouse = Warehouse::where('is_central', true)->firstOrFail();
        $this->projectWarehouse = Warehouse::where('is_central', false)->firstOrFail();
        $this->supplier = Supplier::firstOrFail();
        $this->adminPusat = User::where('email', 'admin.pusat@arsikon.co.id')->firstOrFail();
        $this->adminProyek = User::where('email', 'admin.proyek1@arsikon.co.id')->firstOrFail();
        $this->adminPO = User::where('email', 'admin.po@arsikon.co.id')->firstOrFail();
        $this->karyawan = User::where('email', 'karyawan@arsikon.co.id')->firstOrFail();

        $this->material = Material::firstOrCreate(
            ['sku' => 'MAT-LOSS-001'],
            [
                'name'        => 'Bata Merah Super',
                'category_id' => \App\Models\Category::first()->id,
                'unit_id'     => \App\Models\Unit::first()->id,
            ]
        );

        Storage::fake('public');
    }

    public function test_cannot_declare_loss_without_permission(): void
    {
        $po = PurchaseOrder::create([
            'po_number'             => 'PO-2026-TEST-01',
            'supplier_id'           => $this->supplier->id,
            'supplier_name'         => $this->supplier->name,
            'delivery_warehouse_id' => $this->projectWarehouse->id,
            'order_date'            => now()->toDateString(),
            'status'                => 'sent',
            'created_by'            => $this->adminPO->id,
            'total_amount'          => 100000,
        ]);

        $poItem = PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'material_id'       => $this->material->id,
            'quantity'          => 100,
            'unit_price'        => 1000,
            'total_price'       => 100000,
            'received_qty'      => 0,
            'lost_qty'          => 0,
        ]);

        $gr = GoodsReceipt::create([
            'receipt_number' => 'GR-2026-0001',
            'warehouse_id'   => $this->projectWarehouse->id,
            'supplier_id'    => $this->supplier->id,
            'purchase_order_id' => $po->id,
            'receipt_date'   => now()->toDateString(),
            'status'         => 'confirmed',
            'created_by'     => $this->adminProyek->id,
            'confirmed_by'   => $this->adminProyek->id,
            'received_by_user_id' => $this->adminProyek->id,
        ]);

        $grItem = GoodsReceiptItem::create([
            'goods_receipt_id'       => $gr->id,
            'material_id'            => $this->material->id,
            'purchase_order_item_id' => $poItem->id,
            'qty_shipped'            => 100,
            'qty_received'           => 80,
            'qty_damaged'            => 0,
            'qty_missing'            => 20,
            'missing_status'         => 'unresolved',
            'unit_price'             => 1000,
        ]);

        // Karyawan does not have 'create transit losses'
        $this->actingAs($this->karyawan);
        $file = UploadedFile::fake()->create('surat_jalan_rusak.pdf', 100);
        $response = $this->post(route('transit-losses.store'), [
            'goods_receipt_item_id' => $grItem->id,
            'notes'                 => 'Pecah saat diangkut sopir truk B 1234 CD',
            'evidence'              => $file,
        ]);

        $response->assertStatus(403);
    }

    public function test_declare_loss_requires_evidence_and_notes(): void
    {
        $po = PurchaseOrder::create([
            'po_number'             => 'PO-2026-TEST-02',
            'supplier_id'           => $this->supplier->id,
            'supplier_name'         => $this->supplier->name,
            'delivery_warehouse_id' => $this->projectWarehouse->id,
            'order_date'            => now()->toDateString(),
            'status'                => 'sent',
            'created_by'            => $this->adminPO->id,
            'total_amount'          => 100000,
        ]);

        $poItem = PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'material_id'       => $this->material->id,
            'quantity'          => 100,
            'unit_price'        => 1000,
            'total_price'       => 100000,
            'received_qty'      => 0,
            'lost_qty'          => 0,
        ]);

        $gr = GoodsReceipt::create([
            'receipt_number' => 'GR-2026-0002',
            'warehouse_id'   => $this->projectWarehouse->id,
            'supplier_id'    => $this->supplier->id,
            'purchase_order_id' => $po->id,
            'receipt_date'   => now()->toDateString(),
            'status'         => 'confirmed',
            'created_by'     => $this->adminProyek->id,
            'confirmed_by'   => $this->adminProyek->id,
            'received_by_user_id' => $this->adminProyek->id,
        ]);

        $grItem = GoodsReceiptItem::create([
            'goods_receipt_id'       => $gr->id,
            'material_id'            => $this->material->id,
            'purchase_order_item_id' => $poItem->id,
            'qty_shipped'            => 100,
            'qty_received'           => 80,
            'qty_damaged'            => 0,
            'qty_missing'            => 20,
            'missing_status'         => 'unresolved',
            'unit_price'             => 1000,
        ]);

        $this->actingAs($this->adminProyek);
        $response = $this->post(route('transit-losses.store'), [
            'goods_receipt_item_id' => $grItem->id,
            'notes'                 => '', // empty notes
            // no evidence
        ]);

        $response->assertSessionHasErrors(['notes', 'evidence']);
    }

    public function test_declare_loss_outside_grace_period_requires_admin_pusat(): void
    {
        $po = PurchaseOrder::create([
            'po_number'             => 'PO-2026-TEST-03',
            'supplier_id'           => $this->supplier->id,
            'supplier_name'         => $this->supplier->name,
            'delivery_warehouse_id' => $this->projectWarehouse->id,
            'order_date'            => now()->subDays(10)->toDateString(),
            'status'                => 'sent',
            'created_by'            => $this->adminPO->id,
            'total_amount'          => 100000,
        ]);

        $poItem = PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'material_id'       => $this->material->id,
            'quantity'          => 100,
            'unit_price'        => 1000,
            'total_price'       => 100000,
            'received_qty'      => 0,
            'lost_qty'          => 0,
        ]);

        $gr = GoodsReceipt::create([
            'receipt_number' => 'GR-2026-0003',
            'warehouse_id'   => $this->projectWarehouse->id,
            'supplier_id'    => $this->supplier->id,
            'purchase_order_id' => $po->id,
            'receipt_date'   => now()->subDays(5)->toDateString(), // 5 days ago (> 3 grace days)
            'status'         => 'confirmed',
            'created_by'     => $this->adminProyek->id,
            'confirmed_by'   => $this->adminProyek->id,
            'received_by_user_id' => $this->adminProyek->id,
        ]);

        $grItem = GoodsReceiptItem::create([
            'goods_receipt_id'       => $gr->id,
            'material_id'            => $this->material->id,
            'purchase_order_item_id' => $poItem->id,
            'qty_shipped'            => 100,
            'qty_received'           => 80,
            'qty_damaged'            => 0,
            'qty_missing'            => 20,
            'missing_status'         => 'unresolved',
            'unit_price'             => 1000,
        ]);

        $file = UploadedFile::fake()->create('surat_jalan.pdf', 100);

        // 1. Admin Proyek attempts to declare loss past grace period -> rejected
        $this->actingAs($this->adminProyek);
        $response = $this->post(route('transit-losses.store'), [
            'goods_receipt_item_id' => $grItem->id,
            'notes'                 => 'Hilang saat dibawa ekspedisi ABC',
            'evidence'              => $file,
        ]);
        $response->assertSessionHas('error');

        // 2. Admin Pusat attempts to declare loss past grace period -> allowed
        $this->actingAs($this->adminPusat);
        $responsePusat = $this->post(route('transit-losses.store'), [
            'goods_receipt_item_id' => $grItem->id,
            'notes'                 => 'Otorisasi pencatatan kehilangan oleh Admin Pusat',
            'evidence'              => $file,
        ]);
        $responsePusat->assertSessionHas('success');

        $this->assertDatabaseHas('transit_losses', [
            'goods_receipt_item_id' => $grItem->id,
            'status'                => 'recorded',
            'qty'                   => 20,
        ]);
    }

    public function test_approver_cannot_be_loss_reporter_or_gr_receiver(): void
    {
        $po = PurchaseOrder::create([
            'po_number'             => 'PO-2026-TEST-04',
            'supplier_id'           => $this->supplier->id,
            'supplier_name'         => $this->supplier->name,
            'delivery_warehouse_id' => $this->projectWarehouse->id,
            'order_date'            => now()->toDateString(),
            'status'                => 'sent',
            'created_by'            => $this->adminPO->id,
            'total_amount'          => 100000,
        ]);

        $poItem = PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'material_id'       => $this->material->id,
            'quantity'          => 100,
            'unit_price'        => 1000,
            'total_price'       => 100000,
            'received_qty'      => 0,
            'lost_qty'          => 0,
        ]);

        $gr = GoodsReceipt::create([
            'receipt_number' => 'GR-2026-0004',
            'warehouse_id'   => $this->projectWarehouse->id,
            'supplier_id'    => $this->supplier->id,
            'purchase_order_id' => $po->id,
            'receipt_date'   => now()->toDateString(),
            'status'         => 'confirmed',
            'created_by'     => $this->adminProyek->id,
            'confirmed_by'   => $this->adminProyek->id,
            'received_by_user_id' => $this->adminProyek->id,
        ]);

        $grItem = GoodsReceiptItem::create([
            'goods_receipt_id'       => $gr->id,
            'material_id'            => $this->material->id,
            'purchase_order_item_id' => $poItem->id,
            'qty_shipped'            => 100,
            'qty_received'           => 80,
            'qty_damaged'            => 0,
            'qty_missing'            => 20,
            'missing_status'         => 'unresolved',
            'unit_price'             => 1000,
        ]);

        $transitLoss = TransitLoss::create([
            'goods_receipt_id'       => $gr->id,
            'goods_receipt_item_id'  => $grItem->id,
            'purchase_order_id'      => $po->id,
            'purchase_order_item_id' => $poItem->id,
            'material_id'            => $this->material->id,
            'qty'                    => 20,
            'lost_qty'               => 20,
            'unit_price'             => 1000,
            'loss_value'             => 20000,
            'status'                 => 'recorded',
            'notes'                  => 'Hilang di perjalanan',
            'recorded_by'            => $this->adminPusat->id, // Created by Admin Pusat
        ]);

        // 1. Loss reporter (Admin Pusat) cannot approve own loss report
        $this->actingAs($this->adminPusat);
        $response = $this->post(route('transit-losses.approve', $transitLoss));
        $response->assertSessionHas('error');

        // 2. Change reporter to someone else, but test GR confirmer (adminProyek) cannot approve
        $transitLoss->update(['recorded_by' => $this->adminPO->id]);
        $this->adminProyek->givePermissionTo('approve transit losses');
        $this->actingAs($this->adminProyek);
        $responseProyek = $this->post(route('transit-losses.approve', $transitLoss));
        $responseProyek->assertSessionHas('error');
    }

    public function test_approve_transit_loss_flow_and_quota_update(): void
    {
        $mr = MaterialRequest::create([
            'request_number'   => 'MR-2026-TEST-LOSS',
            'to_warehouse_id'  => $this->projectWarehouse->id,
            'requested_by_user_id' => $this->karyawan->id,
            'needed_at'        => now()->addDays(5)->toDateString(),
            'status'           => 'approved',
        ]);

        $mrItem = MaterialRequestItem::create([
            'material_request_id' => $mr->id,
            'material_id'         => $this->material->id,
            'qty_requested'       => 100,
            'qty_approved'        => 100,
            'qty_ordered'         => 100,
            'qty_received'        => 85,
            'fulfillment'         => 'purchase',
        ]);

        $po = PurchaseOrder::create([
            'po_number'             => 'PO-2026-TEST-05',
            'supplier_id'           => $this->supplier->id,
            'supplier_name'         => $this->supplier->name,
            'delivery_warehouse_id' => $this->projectWarehouse->id,
            'order_date'            => now()->toDateString(),
            'status'                => 'partial_received',
            'created_by'            => $this->adminPO->id,
            'total_amount'          => 100000,
            'risk_bearer'           => 'buyer',
        ]);

        $poItem = PurchaseOrderItem::create([
            'purchase_order_id'       => $po->id,
            'material_id'             => $this->material->id,
            'material_request_item_id'=> $mrItem->id,
            'quantity'                => 100,
            'unit_price'              => 1000,
            'total_price'             => 100000,
            'received_qty'            => 85,
            'lost_qty'                => 0,
        ]);

        $gr = GoodsReceipt::create([
            'receipt_number' => 'GR-2026-0005',
            'warehouse_id'   => $this->projectWarehouse->id,
            'supplier_id'    => $this->supplier->id,
            'purchase_order_id' => $po->id,
            'receipt_date'   => now()->toDateString(),
            'status'         => 'confirmed',
            'created_by'     => $this->adminProyek->id,
            'confirmed_by'   => $this->adminProyek->id,
            'received_by_user_id' => $this->adminProyek->id,
        ]);

        $grItem = GoodsReceiptItem::create([
            'goods_receipt_id'       => $gr->id,
            'material_id'            => $this->material->id,
            'purchase_order_item_id' => $poItem->id,
            'qty_shipped'            => 100,
            'qty_received'           => 85,
            'qty_damaged'            => 0,
            'qty_missing'            => 15,
            'missing_status'         => 'unresolved',
            'unit_price'             => 1000,
        ]);

        // Recorded by Admin Proyek
        $transitLoss = TransitLoss::create([
            'goods_receipt_id'       => $gr->id,
            'goods_receipt_item_id'  => $grItem->id,
            'purchase_order_id'      => $po->id,
            'purchase_order_item_id' => $poItem->id,
            'material_id'            => $this->material->id,
            'qty'                    => 15,
            'lost_qty'               => 15,
            'unit_price'             => 1000,
            'loss_value'             => 15000,
            'status'                 => 'recorded',
            'notes'                  => '15 bata pecah hancur dalam perjalanan',
            'recorded_by'            => $this->adminProyek->id,
        ]);

        // Admin Pusat (berbeda dari pembuat loss dan penerima GR) approves
        $this->actingAs($this->adminPusat);
        $response = $this->post(route('transit-losses.approve', $transitLoss));
        $response->assertSessionHas('success');

        $transitLoss->refresh();
        $this->assertEquals('claim_filed', $transitLoss->status);
        $this->assertEquals($this->adminPusat->id, $transitLoss->approved_by);
        $this->assertNotNull($transitLoss->approved_at);

        $grItem->refresh();
        $this->assertEquals('confirmed_lost', $grItem->missing_status);

        $poItem->refresh();
        $this->assertEquals(15, (float)$poItem->lost_qty);
        // Outstanding is now 0 (100 - 85 - 15 = 0)
        $this->assertEquals(0, (float)$poItem->outstanding);

        $po->refresh();
        // Since outstanding is 0, PO status becomes received
        $this->assertEquals('received', $po->status);

        // Usable stock in project warehouse remains 85
        $stock = Inventory::where('warehouse_id', $this->projectWarehouse->id)
            ->where('material_id', $this->material->id)
            ->value('quantity') ?? 0;
        $this->assertEquals(0, (float)$stock); // stock wasn't manually seeded, but not increased by 15

        // MR qty_received does NOT increase from lost items
        $mrItem->refresh();
        $this->assertEquals(85, (float)$mrItem->qty_received);
    }
}
