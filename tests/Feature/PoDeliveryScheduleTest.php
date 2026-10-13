<?php

namespace Tests\Feature;

use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\Inventory;
use App\Models\Material;
use App\Models\MaterialRequest;
use App\Models\MaterialRequestItem;
use App\Models\PoDeliverySchedule;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\GoodsReceiptService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PoDeliveryScheduleTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminPusat;
    protected User $adminPO;
    protected User $adminProyek;
    protected Warehouse $centralWarehouse;
    protected Warehouse $projectWarehouse;
    protected Supplier $supplier;
    protected Material $material;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $this->centralWarehouse = Warehouse::where('is_central', true)->firstOrFail();
        $this->projectWarehouse = Warehouse::where('is_central', false)->firstOrFail();
        $this->supplier = Supplier::firstOrFail();

        $this->adminPusat = User::where('email', 'admin.pusat@arsikon.co.id')->firstOrFail();
        $this->adminPO = User::where('email', 'admin.po@arsikon.co.id')->firstOrFail();
        $this->adminProyek = User::where('email', 'admin.proyek1@arsikon.co.id')->firstOrFail();

        $this->material = Material::firstOrCreate(
            ['sku' => 'MAT-SCHED-01'],
            [
                'name'         => 'Semen Gresik 50kg Schedule',
                'brand'        => 'Gresik',
                'type'         => 'Semen',
                'category_id'  => \App\Models\Category::first()->id,
                'unit_id'      => \App\Models\Unit::first()->id,
                'is_active'    => true,
            ]
        );
    }

    public function test_po_creation_without_schedules_creates_default_full_schedule(): void
    {
        $mr = MaterialRequest::create([
            'request_number'       => 'MR-2026-SCH-01',
            'to_warehouse_id'      => $this->projectWarehouse->id,
            'requested_by_user_id' => $this->adminProyek->id,
            'needed_at'            => now()->addDays(5)->toDateString(),
            'status'               => 'approved',
        ]);

        $mrItem = MaterialRequestItem::create([
            'material_request_id' => $mr->id,
            'material_id'         => $this->material->id,
            'qty_requested'       => 100,
            'qty_approved'        => 100,
            'qty_ordered'         => 0,
            'fulfillment'         => 'purchase',
        ]);

        $this->actingAs($this->adminPO);

        $response = $this->post(route('purchase-orders.store'), [
            'supplier_id'           => $this->supplier->id,
            'delivery_warehouse_id' => $this->projectWarehouse->id,
            'risk_bearer'           => 'vendor',
            'order_date'            => now()->toDateString(),
            'expected_delivery'     => now()->addDays(7)->toDateString(),
            'items'                 => [
                [
                    'material_id'              => $this->material->id,
                    'material_request_item_id' => $mrItem->id,
                    'quantity'                 => 100,
                    'unit_price'               => 65000,
                    // schedules dibiarkan kosong
                ]
            ]
        ]);

        $response->assertSessionHasNoErrors();

        $po = PurchaseOrder::where('supplier_id', $this->supplier->id)->latest('id')->first();
        $this->assertNotNull($po);
        $poItem = $po->items->first();
        $this->assertNotNull($poItem);

        // Harus ada 1 jadwal pengiriman otomatis
        $schedules = $poItem->deliverySchedules;
        $this->assertCount(1, $schedules);
        $defaultSchedule = $schedules->first();
        $this->assertEquals(100, (float)$defaultSchedule->planned_qty);
        $this->assertEquals(0, (float)$defaultSchedule->received_qty);
        $this->assertEquals('planned', $defaultSchedule->status);
        $this->assertEquals($po->expected_delivery->toDateString(), $defaultSchedule->planned_date->toDateString());
    }

    public function test_po_creation_with_multiple_schedules_validates_total_planned_qty(): void
    {
        $mr = MaterialRequest::create([
            'request_number'       => 'MR-2026-SCH-02',
            'to_warehouse_id'      => $this->projectWarehouse->id,
            'requested_by_user_id' => $this->adminProyek->id,
            'needed_at'            => now()->addDays(5)->toDateString(),
            'status'               => 'approved',
        ]);

        $mrItem = MaterialRequestItem::create([
            'material_request_id' => $mr->id,
            'material_id'         => $this->material->id,
            'qty_requested'       => 100,
            'qty_approved'        => 100,
            'qty_ordered'         => 0,
            'fulfillment'         => 'purchase',
        ]);

        $this->actingAs($this->adminPO);

        // Skenario 1: Total jadwal melebihi quantity (60 + 50 = 110 > 100) -> harus ditolak
        $responseFail = $this->post(route('purchase-orders.store'), [
            'supplier_id'           => $this->supplier->id,
            'delivery_warehouse_id' => $this->projectWarehouse->id,
            'risk_bearer'           => 'vendor',
            'order_date'            => now()->toDateString(),
            'expected_delivery'     => now()->addDays(7)->toDateString(),
            'items'                 => [
                [
                    'material_id'              => $this->material->id,
                    'material_request_item_id' => $mrItem->id,
                    'quantity'                 => 100,
                    'unit_price'               => 65000,
                    'schedules'                => [
                        ['stage_label' => 'Tahap 1', 'planned_date' => now()->addDays(3)->toDateString(), 'planned_qty' => 60],
                        ['stage_label' => 'Tahap 2', 'planned_date' => now()->addDays(7)->toDateString(), 'planned_qty' => 50],
                    ]
                ]
            ]
        ]);
        $responseFail->assertSessionHasErrors(['items.0.schedules']);

        // Skenario 2: Total jadwal pas (60 + 40 = 100) -> harus sukses
        $responseSuccess = $this->post(route('purchase-orders.store'), [
            'supplier_id'           => $this->supplier->id,
            'delivery_warehouse_id' => $this->projectWarehouse->id,
            'risk_bearer'           => 'vendor',
            'order_date'            => now()->toDateString(),
            'expected_delivery'     => now()->addDays(7)->toDateString(),
            'items'                 => [
                [
                    'material_id'              => $this->material->id,
                    'material_request_item_id' => $mrItem->id,
                    'quantity'                 => 100,
                    'unit_price'               => 65000,
                    'schedules'                => [
                        ['stage_label' => 'Tahap 1', 'planned_date' => now()->addDays(3)->toDateString(), 'planned_qty' => 60],
                        ['stage_label' => 'Tahap 2', 'planned_date' => now()->addDays(7)->toDateString(), 'planned_qty' => 40],
                    ]
                ]
            ]
        ]);
        $responseSuccess->assertSessionHasNoErrors();

        $po = PurchaseOrder::where('supplier_id', $this->supplier->id)->latest('id')->first();
        $poItem = $po->items->first();
        $this->assertCount(2, $poItem->deliverySchedules);
        $this->assertEquals(60, (float)$poItem->deliverySchedules[0]->planned_qty);
        $this->assertEquals(40, (float)$poItem->deliverySchedules[1]->planned_qty);
    }

    public function test_update_schedules_only_allowed_when_po_is_draft(): void
    {
        $po = PurchaseOrder::create([
            'po_number'             => 'PO-2026-TEST-DRAFT',
            'supplier_id'           => $this->supplier->id,
            'delivery_warehouse_id' => $this->projectWarehouse->id,
            'status'                => 'draft',
            'order_date'            => now()->toDateString(),
            'expected_delivery'     => now()->addDays(5)->toDateString(),
            'created_by'            => $this->adminPO->id,
            'total_amount'          => 100000,
        ]);

        $poItem = PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'material_id'       => $this->material->id,
            'quantity'          => 100,
            'unit_price'        => 1000,
            'subtotal'          => 100000,
        ]);

        $this->actingAs($this->adminPO);

        // Update jadwal saat PO draft -> berhasil
        $responseDraft = $this->put(route('purchase-orders.update-schedules', $po), [
            'items' => [
                [
                    'purchase_order_item_id' => $poItem->id,
                    'schedules' => [
                        ['stage_label' => 'Tahap A', 'planned_date' => now()->addDays(2)->toDateString(), 'planned_qty' => 50],
                        ['stage_label' => 'Tahap B', 'planned_date' => now()->addDays(6)->toDateString(), 'planned_qty' => 50],
                    ]
                ]
            ]
        ]);
        $responseDraft->assertSessionHas('success');
        $this->assertCount(2, $poItem->fresh()->deliverySchedules);

        // Ubah status PO menjadi approved
        $po->update(['status' => 'approved', 'approved_by' => $this->adminPusat->id, 'approved_at' => now()]);

        // Coba update jadwal saat status bukan draft -> ditolak
        $responseApproved = $this->put(route('purchase-orders.update-schedules', $po), [
            'items' => [
                [
                    'purchase_order_item_id' => $poItem->id,
                    'schedules' => [
                        ['stage_label' => 'Tahap C', 'planned_date' => now()->addDays(2)->toDateString(), 'planned_qty' => 100],
                    ]
                ]
            ]
        ]);
        $responseApproved->assertSessionHas('error');
    }

    public function test_get_scheduled_incoming_reads_from_po_delivery_schedules_not_incoming_stages(): void
    {
        // 1. Material memiliki incoming_stages dummy (tidak boleh muncul)
        $this->material->update([
            'incoming_stages' => [
                ['stage' => 'Tahap Dummy Lama', 'qty' => 999, 'status' => 'planned', 'date' => now()->toDateString()]
            ]
        ]);

        // 2. Buat PO approved dengan 2 jadwal pengiriman
        $po = PurchaseOrder::create([
            'po_number'             => 'PO-2026-SCHED-INCOMING',
            'supplier_id'           => $this->supplier->id,
            'delivery_warehouse_id' => $this->projectWarehouse->id,
            'status'                => 'approved',
            'order_date'            => now()->toDateString(),
            'expected_delivery'     => now()->addDays(3)->toDateString(),
            'created_by'            => $this->adminPO->id,
            'approved_by'           => $this->adminPusat->id,
            'approved_at'           => now(),
            'total_amount'          => 100000,
        ]);

        $poItem = PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'material_id'       => $this->material->id,
            'quantity'          => 80,
            'unit_price'        => 1000,
            'subtotal'          => 80000,
        ]);

        $sch1 = PoDeliverySchedule::create([
            'purchase_order_item_id' => $poItem->id,
            'stage_label'            => 'Pengiriman Batch 1',
            'planned_date'           => now()->toDateString(),
            'planned_qty'            => 50,
            'received_qty'           => 0,
            'warehouse_id'           => $this->projectWarehouse->id,
            'status'                 => 'planned',
        ]);

        $sch2 = PoDeliverySchedule::create([
            'purchase_order_item_id' => $poItem->id,
            'stage_label'            => 'Pengiriman Batch 2',
            'planned_date'           => now()->addDays(5)->toDateString(),
            'planned_qty'            => 30,
            'received_qty'           => 0,
            'warehouse_id'           => $this->projectWarehouse->id,
            'status'                 => 'planned',
        ]);

        $this->actingAs($this->adminProyek);

        // Ambil semua jadwal
        $responseAll = $this->getJson(route('goods-receipts.scheduledIncoming', ['date' => 'all']));
        $responseAll->assertOk();
        $json = $responseAll->json();

        // Data tidak boleh memuat incoming_stages 999
        $quantities = collect($json['items'])->pluck('quantity')->map(fn($q) => (float)$q)->all();
        $this->assertNotContains(999.0, $quantities);
        $this->assertContains(50.0, $quantities);
        $this->assertContains(30.0, $quantities);

        // Ambil filter hari ini saja
        $responseToday = $this->getJson(route('goods-receipts.scheduledIncoming', ['date' => now()->toDateString()]));
        $responseToday->assertOk();
        $jsonToday = $responseToday->json();
        $this->assertEquals(1, $jsonToday['count']);
        $this->assertEquals('Pengiriman Batch 1', $jsonToday['items'][0]['stage_reference']);
    }

    public function test_goods_receipt_confirm_allocates_to_delivery_schedule(): void
    {
        $po = PurchaseOrder::create([
            'po_number'             => 'PO-2026-SCHED-CONFIRM',
            'supplier_id'           => $this->supplier->id,
            'delivery_warehouse_id' => $this->projectWarehouse->id,
            'status'                => 'sent',
            'order_date'            => now()->toDateString(),
            'expected_delivery'     => now()->addDays(4)->toDateString(),
            'created_by'            => $this->adminPO->id,
            'approved_by'           => $this->adminPusat->id,
            'approved_at'           => now(),
            'total_amount'          => 100000,
        ]);

        $poItem = PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'material_id'       => $this->material->id,
            'quantity'          => 100,
            'unit_price'        => 1000,
            'subtotal'          => 100000,
        ]);

        $sch1 = PoDeliverySchedule::create([
            'purchase_order_item_id' => $poItem->id,
            'stage_label'            => 'Jadwal Pertama',
            'planned_date'           => now()->toDateString(),
            'planned_qty'            => 40,
            'received_qty'           => 0,
            'warehouse_id'           => $this->projectWarehouse->id,
            'status'                 => 'planned',
        ]);

        $sch2 = PoDeliverySchedule::create([
            'purchase_order_item_id' => $poItem->id,
            'stage_label'            => 'Jadwal Kedua',
            'planned_date'           => now()->addDays(3)->toDateString(),
            'planned_qty'            => 60,
            'received_qty'           => 0,
            'warehouse_id'           => $this->projectWarehouse->id,
            'status'                 => 'planned',
        ]);

        // Buat GR tanpa memilih jadwal secara manual (auto-alokasi ke jadwal terdekat)
        $gr = GoodsReceipt::create([
            'receipt_number'        => 'GR-2026-TEST-SCH',
            'supplier_id'           => $this->supplier->id,
            'warehouse_id'          => $this->projectWarehouse->id,
            'purchase_order_id'     => $po->id,
            'receipt_date'          => now()->toDateString(),
            'status'                => 'draft',
            'created_by'            => $this->adminProyek->id,
            'received_by_user_id'   => $this->adminProyek->id,
        ]);

        $grItem = GoodsReceiptItem::create([
            'goods_receipt_id'        => $gr->id,
            'item_type'               => 'material',
            'purchase_order_item_id'  => $poItem->id,
            'material_id'             => $this->material->id,
            'qty_shipped'             => 40,
            'qty_received'            => 40,
            'qty_damaged'             => 0,
            'qty_missing'             => 0,
            'missing_status'          => 'none',
            'unit_price'              => 1000,
        ]);

        // Confirm Goods Receipt
        $grService = app(GoodsReceiptService::class);
        $grService->confirm($gr, $this->adminProyek->id);

        // Jadwal pertama harus tuntas teralokasi (status 'received')
        $sch1->refresh();
        $this->assertEquals(40, (float)$sch1->received_qty);
        $this->assertEquals('received', $sch1->status);

        // Jadwal kedua tetap planned dengan received_qty 0
        $sch2->refresh();
        $this->assertEquals(0, (float)$sch2->received_qty);
        $this->assertEquals('planned', $sch2->status);

        // Stok fisik gudang bertambah 40
        $stock = Inventory::where('warehouse_id', $this->projectWarehouse->id)
            ->where('material_id', $this->material->id)
            ->value('quantity') ?? 0;
        $this->assertEquals(40, (float)$stock);
    }

    public function test_check_late_delivery_schedules_command_notifies_once(): void
    {
        $po = PurchaseOrder::create([
            'po_number'             => 'PO-2026-LATE-TEST',
            'supplier_id'           => $this->supplier->id,
            'delivery_warehouse_id' => $this->projectWarehouse->id,
            'status'                => 'sent',
            'order_date'            => now()->subDays(5)->toDateString(),
            'expected_delivery'     => now()->subDays(2)->toDateString(),
            'created_by'            => $this->adminPO->id,
            'approved_by'           => $this->adminPusat->id,
            'approved_at'           => now()->subDays(5),
            'total_amount'          => 50000,
        ]);

        $poItem = PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'material_id'       => $this->material->id,
            'quantity'          => 50,
            'unit_price'        => 1000,
            'subtotal'          => 50000,
        ]);

        $schedule = PoDeliverySchedule::create([
            'purchase_order_item_id' => $poItem->id,
            'stage_label'            => 'Jadwal Kemarin Dulu',
            'planned_date'           => now()->subDays(2)->toDateString(),
            'planned_qty'            => 50,
            'received_qty'           => 0,
            'warehouse_id'           => $this->projectWarehouse->id,
            'status'                 => 'planned',
            'late_notified_at'       => null,
        ]);

        $this->assertTrue($schedule->is_late);

        // Jalankan perintah console pertama kali -> harus mengirim notifikasi
        $this->artisan('procurement:check-late-schedules')->assertExitCode(0);

        $schedule->refresh();
        $this->assertNotNull($schedule->late_notified_at);

        // Jalankan perintah console kedua kali -> tidak boleh mengirim notifikasi duplikat
        $this->artisan('procurement:check-late-schedules')
            ->expectsOutput('Tidak ada jadwal kedatangan terlambat yang perlu dinotifikasikan.')
            ->assertExitCode(0);
    }

    public function test_material_update_does_not_overwrite_incoming_stages_and_no_stock_bypass(): void
    {
        $category = \App\Models\Category::first();
        $unit = \App\Models\Unit::first();

        $material = Material::create([
            'sku'             => 'MAT-FROZEN-01',
            'name'            => 'Semen Frozen Test',
            'type'            => 'Semen',
            'category_id'     => $category->id,
            'unit_id'         => $unit->id,
            'incoming_stages' => [
                ['stage' => 'Tahap A Historis', 'qty' => 50, 'status' => 'received', 'date' => '2026-01-01']
            ],
            'is_active'       => true,
        ]);

        $this->actingAs($this->adminPusat);

        // Update nama material dengan payload tanpa incoming_stages atau incoming_stages baru
        $response = $this->put(route('materials.update', $material), [
            'name'            => 'Semen Frozen Test Updated',
            'sku'             => 'MAT-FROZEN-01',
            'type'            => 'Semen',
            'category_id'     => $category->id,
            'unit_id'         => (string) $unit->id,
            'warehouse_id'    => $this->centralWarehouse->id,
            'incoming_stages' => [
                ['stage' => 'Tahap Baru Diubah', 'qty' => 999, 'status' => 'planned', 'date' => '2026-12-31']
            ]
        ]);

        $response->assertSessionHasNoErrors();

        $material->refresh();
        $this->assertEquals('Semen Frozen Test Updated', $material->name);
        // incoming_stages TIDAK berubah / tidak ditimpa
        $this->assertEquals('Tahap A Historis', $material->incoming_stages[0]['stage']);
        $this->assertCount(1, $material->incoming_stages);
    }
}
