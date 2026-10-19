<?php

namespace Tests\Feature;

use App\Models\Material;
use App\Models\MaterialRequest;
use App\Models\MaterialRequestItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseReceipt;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use Database\Seeders\MasterDataSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseOrderReceiptIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminPO;
    protected User $adminPusat;
    protected Supplier $supplier;
    protected Warehouse $warehouse;
    protected Material $material;
    protected MaterialRequest $mr;
    protected MaterialRequestItem $mrItem;
    protected PurchaseOrder $po;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(MasterDataSeeder::class);

        $this->adminPO = User::firstOrCreate(
            ['email' => 'admin.po.receipt@example.com'],
            ['name' => 'Admin PO Pengadaan', 'password' => bcrypt('password')]
        );
        $this->adminPO->syncRoles(['Admin PO']);

        $this->adminPusat = User::firstOrCreate(
            ['email' => 'admin.pusat.receipt@example.com'],
            ['name' => 'Admin Pusat', 'password' => bcrypt('password')]
        );
        $this->adminPusat->syncRoles(['Admin Pusat']);

        $this->supplier = Supplier::first() ?? Supplier::create([
            'name'  => 'PT Toko Bangunan Abadi',
            'phone' => '081234567800',
        ]);

        $this->warehouse = Warehouse::where('is_central', true)->first() ?? Warehouse::create([
            'name'       => 'Gudang Pusat Utama',
            'code'       => 'WH-PST-MAIN',
            'is_central' => true,
        ]);

        $category = \App\Models\Category::firstOrCreate(
            ['code' => 'CAT-BESI'],
            ['name' => 'Besi Beton', 'type' => 'material']
        );
        $unit = \App\Models\Unit::firstOrCreate(
            ['code' => 'BTG'],
            ['name' => 'Batang', 'is_decimal' => false]
        );
        $this->material = Material::firstOrCreate(
            ['sku' => 'MAT-BESI-10'],
            [
                'name'        => 'Besi Beton 10mm Sni',
                'category_id' => $category->id,
                'unit_id'     => $unit->id,
            ]
        );

        $this->mr = MaterialRequest::create([
            'request_number'       => 'MR-INTEGRATION-001',
            'requested_by_user_id' => $this->adminPO->id,
            'to_warehouse_id'      => $this->warehouse->id,
            'status'               => 'approved',
            'notes'                => 'MR Pengadaan Besi',
        ]);

        $this->mrItem = MaterialRequestItem::create([
            'material_request_id' => $this->mr->id,
            'material_id'         => $this->material->id,
            'qty_requested'       => 100,
            'qty_approved'        => 100,
            'qty_ordered'         => 0,
            'fulfillment'         => 'purchase',
        ]);

        // Buat PO
        $this->actingAs($this->adminPO)->post(route('purchase-orders.store'), [
            'supplier_id'           => $this->supplier->id,
            'material_request_id'   => $this->mr->id,
            'delivery_warehouse_id' => $this->warehouse->id,
            'order_date'            => '2026-10-19',
            'contact_person'        => 'Bpk. Agus (0812-3344-5566)',
            'notes'                 => 'Pengadaan besi tahap 1',
            'items'                 => [
                [
                    'material_id'              => $this->material->id,
                    'material_request_item_id' => $this->mrItem->id,
                    'quantity'                 => 25,
                    'unit_price'               => 75000,
                ],
            ],
        ]);

        $this->po = PurchaseOrder::where('material_request_id', $this->mr->id)->latest('id')->firstOrFail();
    }

    public function test_create_receipt_page_with_po_id_automatically_populates_items_from_po(): void
    {
        $response = $this->actingAs($this->adminPO)->get(route('purchase-receipts.create', ['po_id' => $this->po->id]));

        $response->assertStatus(200);
        // Memastikan nomor PO dan banner PO muncul
        $response->assertSee($this->po->po_number);
        // Memastikan rincian barang dari PO otomatis terisi di Blade
        $response->assertSee('Besi Beton 10mm Sni');
        $response->assertSee('75000');
        $response->assertSee('25');
        // Memastikan nama supplier dan proyek otomatis terpilih
        $response->assertSee($this->supplier->name);
    }

    public function test_user_can_submit_purchase_receipt_prefilled_from_po(): void
    {
        $response = $this->actingAs($this->adminPO)->post(route('purchase-receipts.store'), [
            'purchase_order_id' => $this->po->id,
            'project_name'      => $this->warehouse->name,
            'supplier_name'     => $this->supplier->name,
            'receipt_number'    => 'NOTA-F-9901',
            'receipt_date'      => '2026-10-19',
            'payment_status'    => 'unpaid',
            'items'             => [
                [
                    'material_id' => $this->material->id,
                    'item_name'   => 'Besi Beton 10mm Sni',
                    'quantity'    => 25,
                    'unit_price'  => 75000,
                    'notes'       => 'Sesuai fisik faktur toko',
                ],
            ],
        ]);

        $response->assertSessionHasNoErrors();

        $receipt = PurchaseReceipt::where('purchase_order_id', $this->po->id)->first();
        $this->assertNotNull($receipt);
        $this->assertEquals('NOTA-F-9901', $receipt->receipt_number);
        $this->assertEquals(1875000, (float) $receipt->total_amount);
        $this->assertEquals('Senin', $receipt->day_label);
        $this->assertCount(1, $receipt->items);

        // Muncul pada halaman detail PO
        $showPo = $this->actingAs($this->adminPO)->get(route('purchase-orders.show', $this->po));
        $showPo->assertStatus(200);
        $showPo->assertSee('NOTA-F-9901');
    }
}
