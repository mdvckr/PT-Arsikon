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
use App\Services\GoodsReceiptService;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoodsReceiptMultiPoVendorTest extends TestCase
{
    use RefreshDatabase;

    protected GoodsReceiptService $goodsReceiptService;
    protected Warehouse $projectWarehouse;
    protected Warehouse $centralWarehouse;
    protected Supplier $vendorA;
    protected Supplier $vendorB;
    protected User $adminUser;
    protected User $fieldUser;
    protected Category $category;
    protected Unit $unit;
    protected Material $item1;
    protected Material $item2;
    protected Material $item3;
    protected Material $item4;
    protected Material $item5;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $this->goodsReceiptService = new GoodsReceiptService(new StockService());
        $this->centralWarehouse = Warehouse::where('is_central', true)->firstOrFail();
        $this->projectWarehouse = Warehouse::where('is_central', false)->firstOrFail();

        $this->vendorA = Supplier::firstOrCreate(['name' => 'PT Vendor Material A'], ['phone' => '0811111111']);
        $this->vendorB = Supplier::firstOrCreate(['name' => 'CV Vendor Material B'], ['phone' => '0822222222']);

        $this->adminUser = User::where('email', 'admin.pusat@arsikon.co.id')->firstOrFail();
        $this->fieldUser = User::where('email', 'admin.proyek1@arsikon.co.id')->firstOrFail();

        $this->category = Category::first() ?? Category::create(['code' => 'CAT-01', 'name' => 'Material Konstruksi']);
        $this->unit = Unit::first() ?? Unit::create(['name' => 'Batang', 'symbol' => 'btg']);

        $this->item1 = Material::firstOrCreate(['sku' => 'MAT-ITM-001'], ['name' => 'Semen Padang 50kg', 'category_id' => $this->category->id, 'unit_id' => $this->unit->id]);
        $this->item2 = Material::firstOrCreate(['sku' => 'MAT-ITM-002'], ['name' => 'Pasir Pasang', 'category_id' => $this->category->id, 'unit_id' => $this->unit->id]);
        $this->item3 = Material::firstOrCreate(['sku' => 'MAT-ITM-003'], ['name' => 'Besi Ulir 10mm', 'category_id' => $this->category->id, 'unit_id' => $this->unit->id]);
        $this->item4 = Material::firstOrCreate(['sku' => 'MAT-ITM-004'], ['name' => 'Besi Ulir 12mm', 'category_id' => $this->category->id, 'unit_id' => $this->unit->id]);
        $this->item5 = Material::firstOrCreate(['sku' => 'MAT-ITM-005'], ['name' => 'Kawat Bendrat', 'category_id' => $this->category->id, 'unit_id' => $this->unit->id]);
    }

    /**
     * Test alur lengkap PO Lapangan 5 barang, dibeli terpisah via 2 Vendor PO:
     * - Vendor A (PO-1): Item 1 dan Item 2
     * - Vendor B (PO-2): Item 3, Item 4, dan Item 5
     *
     * Penerimaan 1 hanya menerima barang PO-1 (Vendor A).
     * MR status TIDAK BOLEH 'fulfilled', melainkan harus 'partially_fulfilled'.
     * PO-1 berstatus 'received', sementara PO-2 tetap pending.
     * Ketika PO-2 datang, endpoint filter PO memuat barang PO-2 dengan vendor B.
     * Penerimaan 2 menerima sisa barang PO-2, barulah MR berstatus 'fulfilled'.
     */
    public function test_multi_po_vendor_goods_receipt_lifecycle(): void
    {
        // 1. Lapangan membuat PO Lapangan (MR) dengan 5 barang
        $mr = MaterialRequest::create([
            'request_number'       => 'MR-PROJ-TEST-001',
            'to_warehouse_id'      => $this->projectWarehouse->id,
            'requested_by_user_id' => $this->fieldUser->id,
            'status'               => 'approved',
            'approved_by_user_id'  => $this->adminUser->id,
            'approved_at'          => now(),
        ]);

        $mrItem1 = MaterialRequestItem::create(['material_request_id' => $mr->id, 'material_id' => $this->item1->id, 'qty_requested' => 50, 'qty_approved' => 50, 'qty_received' => 0, 'fulfillment' => 'purchase']);
        $mrItem2 = MaterialRequestItem::create(['material_request_id' => $mr->id, 'material_id' => $this->item2->id, 'qty_requested' => 10, 'qty_approved' => 10, 'qty_received' => 0, 'fulfillment' => 'purchase']);
        $mrItem3 = MaterialRequestItem::create(['material_request_id' => $mr->id, 'material_id' => $this->item3->id, 'qty_requested' => 100, 'qty_approved' => 100, 'qty_received' => 0, 'fulfillment' => 'purchase']);
        $mrItem4 = MaterialRequestItem::create(['material_request_id' => $mr->id, 'material_id' => $this->item4->id, 'qty_requested' => 100, 'qty_approved' => 100, 'qty_received' => 0, 'fulfillment' => 'purchase']);
        $mrItem5 = MaterialRequestItem::create(['material_request_id' => $mr->id, 'material_id' => $this->item5->id, 'qty_requested' => 20, 'qty_approved' => 20, 'qty_received' => 0, 'fulfillment' => 'purchase']);

        // 2. Pengadaan memecah pembelian ke 2 Vendor PO:
        // PO-1 (Vendor A) untuk Item 1 dan Item 2
        $po1 = PurchaseOrder::create([
            'po_number'             => 'PO-VEND-A-001',
            'supplier_id'           => $this->vendorA->id,
            'supplier_name'         => $this->vendorA->name,
            'delivery_warehouse_id' => $this->projectWarehouse->id,
            'material_request_id'   => $mr->id,
            'order_date'            => now()->toDateString(),
            'status'                => 'sent',
            'created_by'            => $this->adminUser->id,
            'total_amount'          => 5000000,
        ]);
        $po1Item1 = PurchaseOrderItem::create(['purchase_order_id' => $po1->id, 'material_request_item_id' => $mrItem1->id, 'material_id' => $this->item1->id, 'quantity' => 50, 'received_qty' => 0, 'unit_price' => 70000]);
        $po1Item2 = PurchaseOrderItem::create(['purchase_order_id' => $po1->id, 'material_request_item_id' => $mrItem2->id, 'material_id' => $this->item2->id, 'quantity' => 10, 'received_qty' => 0, 'unit_price' => 150000]);

        // PO-2 (Vendor B) untuk Item 3, Item 4, dan Item 5
        $po2 = PurchaseOrder::create([
            'po_number'             => 'PO-VEND-B-002',
            'supplier_id'           => $this->vendorB->id,
            'supplier_name'         => $this->vendorB->name,
            'delivery_warehouse_id' => $this->projectWarehouse->id,
            'material_request_id'   => $mr->id,
            'order_date'            => now()->toDateString(),
            'status'                => 'sent',
            'created_by'            => $this->adminUser->id,
            'total_amount'          => 12000000,
        ]);
        $po2Item3 = PurchaseOrderItem::create(['purchase_order_id' => $po2->id, 'material_request_item_id' => $mrItem3->id, 'material_id' => $this->item3->id, 'quantity' => 100, 'received_qty' => 0, 'unit_price' => 50000]);
        $po2Item4 = PurchaseOrderItem::create(['purchase_order_id' => $po2->id, 'material_request_item_id' => $mrItem4->id, 'material_id' => $this->item4->id, 'quantity' => 100, 'received_qty' => 0, 'unit_price' => 65000]);
        $po2Item5 = PurchaseOrderItem::create(['purchase_order_id' => $po2->id, 'material_request_item_id' => $mrItem5->id, 'material_id' => $this->item5->id, 'quantity' => 20, 'received_qty' => 0, 'unit_price' => 25000]);

        // 3. Test Endpoint AJAX getMrItems tanpa filter (Menampilkan list PO yang terhubung)
        $response = $this->actingAs($this->adminUser)
            ->getJson(route('goods-receipts.mrItems', $mr));

        $response->assertOk();
        $data = $response->json();
        $this->assertCount(2, $data['purchase_orders']);
        $this->assertNull($data['purchase_order_id'], 'Multi-vendor PO Lapangan tidak boleh mengunci salah satu PO secara sepihak');

        // 4. Test Endpoint AJAX getMrItems difilter spesifik PO-1 (Vendor A)
        $resPo1 = $this->actingAs($this->adminUser)
            ->getJson(route('goods-receipts.mrItems', [$mr, 'purchase_order_id' => $po1->id]));

        $resPo1->assertOk();
        $dataPo1 = $resPo1->json();
        $this->assertEquals($po1->id, $dataPo1['purchase_order_id']);
        $this->assertEquals($this->vendorA->id, $dataPo1['supplier_id']);
        $this->assertEquals($this->vendorA->name, $dataPo1['supplier_name']);
        // Hanya 2 barang dari Vendor A yang dimuat
        $this->assertCount(2, $dataPo1['items']);
        $itemNamesPo1 = collect($dataPo1['items'])->pluck('material_name')->all();
        $this->assertContains('Semen Padang 50kg', $itemNamesPo1);
        $this->assertContains('Pasir Pasang', $itemNamesPo1);
        $this->assertNotContains('Besi Ulir 10mm', $itemNamesPo1);

        // 5. Test Endpoint AJAX getMrItems difilter spesifik PO-2 (Vendor B)
        $resPo2 = $this->actingAs($this->adminUser)
            ->getJson(route('goods-receipts.mrItems', [$mr, 'purchase_order_id' => $po2->id]));

        $resPo2->assertOk();
        $dataPo2 = $resPo2->json();
        $this->assertEquals($po2->id, $dataPo2['purchase_order_id']);
        $this->assertEquals($this->vendorB->id, $dataPo2['supplier_id']);
        $this->assertEquals($this->vendorB->name, $dataPo2['supplier_name']);
        // Hanya 3 barang dari Vendor B yang dimuat
        $this->assertCount(3, $dataPo2['items']);
        $itemNamesPo2 = collect($dataPo2['items'])->pluck('material_name')->all();
        $this->assertContains('Besi Ulir 10mm', $itemNamesPo2);
        $this->assertContains('Besi Ulir 12mm', $itemNamesPo2);
        $this->assertContains('Kawat Bendrat', $itemNamesPo2);
        $this->assertNotContains('Semen Padang 50kg', $itemNamesPo2);

        // 6. Vendor A tiba duluan di gudang proyek. Petugas mencatat Penerimaan Barang (GR-1) untuk PO-1
        $gr1 = $this->goodsReceiptService->create([
            'purchase_order_id'   => $po1->id,
            'material_request_id' => $mr->id,
            'supplier_id'         => $this->vendorA->id,
            'warehouse_id'        => $this->projectWarehouse->id,
            'received_at'         => now()->toDateString(),
            'received_by_name'    => 'Petugas Gudang Proyek',
            'notes'               => 'Penerimaan Parsial Tahap 1 dari Vendor A',
            'items'               => [
                [
                    'material_id'              => $this->item1->id,
                    'purchase_order_item_id'   => $po1Item1->id,
                    'material_request_item_id' => $mrItem1->id,
                    'qty_shipped'              => 50,
                    'qty_received'             => 50,
                    'qty_damaged'              => 0,
                    'qty_missing'              => 0,
                    'unit_price'               => 70000,
                ],
                [
                    'material_id'              => $this->item2->id,
                    'purchase_order_item_id'   => $po1Item2->id,
                    'material_request_item_id' => $mrItem2->id,
                    'qty_shipped'              => 10,
                    'qty_received'             => 10,
                    'qty_damaged'              => 0,
                    'qty_missing'              => 0,
                    'unit_price'               => 150000,
                ],
            ],
        ], $this->adminUser->id);

        $this->assertEquals('draft', $gr1->status);

        // Konfirmasi GR-1
        $this->goodsReceiptService->confirm($gr1, $this->adminUser->id);

        // Verifikasi Status Pasca Penerimaan 1:
        $po1->refresh();
        $po2->refresh();
        $mr->refresh();

        $this->assertEquals('received', $po1->status, 'PO-1 harus received karena seluruh itemnya telah diterima');
        $this->assertEquals('sent', $po2->status, 'PO-2 harus TETAP sent karena barang dari Vendor B belum tiba!');
        $this->assertEquals('partially_fulfilled', $mr->status, 'PO Lapangan TIDAK BOLEH fulfilled karena baru 2 dari 5 barang yang datang!');

        $mrItem1->refresh();
        $mrItem3->refresh();
        $this->assertEquals(50, (float)$mrItem1->qty_received);
        $this->assertEquals(0, (float)$mrItem3->qty_received);

        // 7. Beberapa hari kemudian, Vendor B tiba membawa barang PO-2 (Item 3, 4, 5)
        $gr2 = $this->goodsReceiptService->create([
            'purchase_order_id'   => $po2->id,
            'material_request_id' => $mr->id,
            'supplier_id'         => $this->vendorB->id,
            'warehouse_id'        => $this->projectWarehouse->id,
            'received_at'         => now()->toDateString(),
            'received_by_name'    => 'Petugas Gudang Proyek',
            'notes'               => 'Penerimaan Tahap 2 dari Vendor B',
            'items'               => [
                [
                    'material_id'              => $this->item3->id,
                    'purchase_order_item_id'   => $po2Item3->id,
                    'material_request_item_id' => $mrItem3->id,
                    'qty_shipped'              => 100,
                    'qty_received'             => 100,
                    'qty_damaged'              => 0,
                    'qty_missing'              => 0,
                    'unit_price'               => 50000,
                ],
                [
                    'material_id'              => $this->item4->id,
                    'purchase_order_item_id'   => $po2Item4->id,
                    'material_request_item_id' => $mrItem4->id,
                    'qty_shipped'              => 100,
                    'qty_received'             => 100,
                    'qty_damaged'              => 0,
                    'qty_missing'              => 0,
                    'unit_price'               => 65000,
                ],
                [
                    'material_id'              => $this->item5->id,
                    'purchase_order_item_id'   => $po2Item5->id,
                    'material_request_item_id' => $mrItem5->id,
                    'qty_shipped'              => 20,
                    'qty_received'             => 20,
                    'qty_damaged'              => 0,
                    'qty_missing'              => 0,
                    'unit_price'               => 25000,
                ],
            ],
        ], $this->adminUser->id);

        // Konfirmasi GR-2
        $this->goodsReceiptService->confirm($gr2, $this->adminUser->id);

        // Verifikasi Status Akhir:
        $po1->refresh();
        $po2->refresh();
        $mr->refresh();

        $this->assertEquals('received', $po1->status);
        $this->assertEquals('received', $po2->status, 'PO-2 sekarang harus received');
        $this->assertEquals('fulfilled', $mr->status, 'PO Lapangan sekarang harus fulfilled karena seluruh 5 barang telah lengkap diterima!');

        $mrItem3->refresh();
        $mrItem4->refresh();
        $mrItem5->refresh();
        $this->assertEquals(100, (float)$mrItem3->qty_received);
        $this->assertEquals(100, (float)$mrItem4->qty_received);
        $this->assertEquals(20, (float)$mrItem5->qty_received);
    }
}
