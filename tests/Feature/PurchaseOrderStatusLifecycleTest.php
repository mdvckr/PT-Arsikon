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
use App\Services\GoodsReceiptService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseOrderStatusLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $adminPO;
    protected Supplier $supplier;
    protected Warehouse $warehouse;
    protected Material $material;
    protected GoodsReceiptService $receiptService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleAndPermissionSeeder::class);
        $this->seed(\Database\Seeders\MasterDataSeeder::class);

        $this->adminUser = User::firstOrCreate(
            ['email' => 'admin.lc@example.com'],
            ['name' => 'Admin LC', 'password' => bcrypt('password')]
        );
        $this->adminUser->syncRoles(['Admin Pusat']);

        $this->adminPO = User::firstOrCreate(
            ['email' => 'admin.po.lc@example.com'],
            ['name' => 'Admin PO LC', 'password' => bcrypt('password')]
        );
        $this->adminPO->syncRoles(['Admin PO', 'Admin Pusat']);

        $this->supplier  = Supplier::first() ?? Supplier::create(['name' => 'PT Mitra LC', 'phone' => '0812345']);
        $this->warehouse = Warehouse::where('type', 'project')->first() ?? Warehouse::first();

        $category = Category::where('type', 'material')->first() ?? Category::first();
        $unit     = Unit::first();

        $this->material = Material::firstOrCreate(
            ['sku' => 'MAT-TEST-LC'],
            [
                'name'              => 'Batu Alam Candi',
                'category_id'       => $category->id,
                'unit_id'           => $unit->id,
                'min_stock_central' => 10,
            ]
        );

        $this->receiptService = app(GoodsReceiptService::class);
    }

    public function test_po_created_from_material_request_starts_with_waiting_delivery_status(): void
    {
        // 1. Buat MR yang disetujui
        $mr = MaterialRequest::create([
            'request_number'       => 'REQ-20261101-9999',
            'to_warehouse_id'      => $this->warehouse->id,
            'requested_by_user_id' => $this->adminUser->id,
            'approved_by_user_id'  => $this->adminUser->id,
            'approved_at'          => now(),
            'status'               => 'approved',
            'needed_at'            => now()->addDays(3)->toDateString(),
        ]);

        $mrItem = MaterialRequestItem::create([
            'material_request_id' => $mr->id,
            'material_id'         => $this->material->id,
            'qty_requested'       => 20,
            'qty_approved'        => 20,
            'qty_ordered'         => 0,
            'fulfillment'         => 'purchase',
        ]);

        // 2. Terbitkan PO dari MR via route store
        $response = $this->actingAs($this->adminPO)->post(route('purchase-orders.store'), [
            'material_request_id'   => $mr->id,
            'supplier_id'           => $this->supplier->id,
            'delivery_warehouse_id' => $this->warehouse->id,
            'order_date'            => now()->toDateString(),
            'expected_delivery'     => now()->addDays(2)->toDateString(),
            'items'                 => [
                [
                    'material_id'              => $this->material->id,
                    'material_request_item_id' => $mrItem->id,
                    'quantity'                 => 20,
                    'unit_price'               => 50000,
                ],
            ],
        ]);

        $response->assertSessionHasNoErrors();
        $po = PurchaseOrder::where('material_request_id', $mr->id)->latest('id')->firstOrFail();

        // 3. Verifikasi status adalah 'sent' dan label adalah "Menunggu Pengiriman" (badge warning)
        $this->assertEquals('sent', $po->status);
        $this->assertEquals('Menunggu Pengiriman', $po->status_label);
        $this->assertEquals('warning', $po->status_color);

        // 4. Periksa halaman index purchase orders menampilkan badge Menunggu Pengiriman
        $resIndex = $this->actingAs($this->adminPO)->get(route('purchase-orders.index'));
        $resIndex->assertOk();
        $resIndex->assertSee('Menunggu Pengiriman');
        $resIndex->assertSee($po->po_number);
    }

    public function test_goods_receipt_transitions_po_and_mr_to_selesai(): void
    {
        // 1. Buat MR yang disetujui
        $mr = MaterialRequest::create([
            'request_number'       => 'REQ-20261101-8888',
            'to_warehouse_id'      => $this->warehouse->id,
            'requested_by_user_id' => $this->adminUser->id,
            'approved_by_user_id'  => $this->adminUser->id,
            'approved_at'          => now(),
            'status'               => 'approved',
            'needed_at'            => now()->addDays(3)->toDateString(),
        ]);

        $mrItem = MaterialRequestItem::create([
            'material_request_id' => $mr->id,
            'material_id'         => $this->material->id,
            'qty_requested'       => 10,
            'qty_approved'        => 10,
            'qty_ordered'         => 10,
            'fulfillment'         => 'purchase',
        ]);

        // 2. Buat PO aktif berstatus 'sent' (Menunggu Pengiriman)
        $po = PurchaseOrder::create([
            'po_number'             => 'PO-2026-TEST-E2E',
            'material_request_id'   => $mr->id,
            'supplier_id'           => $this->supplier->id,
            'supplier_name'         => $this->supplier->name,
            'delivery_warehouse_id' => $this->warehouse->id,
            'status'                => 'sent',
            'order_date'            => now()->toDateString(),
            'total_amount'          => 500000,
            'created_by'            => $this->adminPO->id,
            'approved_by'           => $this->adminPO->id,
            'approved_at'           => now(),
        ]);

        $poItem = PurchaseOrderItem::create([
            'purchase_order_id'        => $po->id,
            'material_id'              => $this->material->id,
            'material_request_item_id' => $mrItem->id,
            'quantity'                 => 10,
            'unit_price'               => 50000,
            'subtotal'                 => 500000,
            'received_qty'             => 0,
        ]);

        $this->assertEquals('Menunggu Pengiriman', $po->status_label);

        // 3. Penerimaan sebagian (5 dari 10)
        $receiptPartial = $this->receiptService->create([
            'purchase_order_id'   => $po->id,
            'material_request_id' => $mr->id,
            'supplier_id'         => $this->supplier->id,
            'warehouse_id'        => $this->warehouse->id,
            'received_at'         => now()->toDateString(),
            'items'               => [
                [
                    'material_id'            => $this->material->id,
                    'purchase_order_item_id' => $poItem->id,
                    'quantity'               => 5,
                    'unit_price'             => 50000,
                ],
            ],
        ], $this->adminUser->id);

        $this->receiptService->confirm($receiptPartial, $this->adminUser->id);

        $po->refresh();
        $this->assertEquals('partial_received', $po->status);
        $this->assertEquals('Sebagian Diterima', $po->status_label);

        // 4. Penerimaan sisa (5 lagi sehingga tuntas 10)
        $receiptFull = $this->receiptService->create([
            'purchase_order_id'   => $po->id,
            'material_request_id' => $mr->id,
            'supplier_id'         => $this->supplier->id,
            'warehouse_id'        => $this->warehouse->id,
            'received_at'         => now()->toDateString(),
            'items'               => [
                [
                    'material_id'            => $this->material->id,
                    'purchase_order_item_id' => $poItem->id,
                    'quantity'               => 5,
                    'unit_price'             => 50000,
                ],
            ],
        ], $this->adminUser->id);

        $this->receiptService->confirm($receiptFull, $this->adminUser->id);

        // 5. Verifikasi PO status menjadi 'received' dengan label 'Selesai'
        $po->refresh();
        $this->assertEquals('received', $po->status);
        $this->assertEquals('Selesai', $po->status_label);
        $this->assertEquals('success', $po->status_color);

        // 6. Verifikasi MR status menjadi 'fulfilled'
        $mr->refresh();
        $this->assertEquals('fulfilled', $mr->status);

        // 7. Periksa tampilan daftar PO menampilkan label Selesai
        $resIndexAfter = $this->actingAs($this->adminPO)->get(route('purchase-orders.index'));
        $resIndexAfter->assertOk();
        $resIndexAfter->assertSee('Selesai');
    }
}
