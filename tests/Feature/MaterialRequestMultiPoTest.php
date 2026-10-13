<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Material;
use App\Models\MaterialRequest;
use App\Models\MaterialRequestItem;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Database\Seeders\MasterDataSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaterialRequestMultiPoTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminPusat;
    protected User $adminPO;
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
            ['email' => 'admin.mr.multi@example.com'],
            ['name' => 'Admin MR Multi', 'password' => bcrypt('password')]
        );
        $this->adminPusat->syncRoles(['Admin Pusat']);

        $this->adminPO = User::firstOrCreate(
            ['email' => 'adminpo.mr.multi@example.com'],
            ['name' => 'Admin PO MR Multi', 'password' => bcrypt('password')]
        );
        $this->adminPO->syncRoles(['Admin PO']);

        $this->supplier = Supplier::first() ?? Supplier::create([
            'name' => 'PT Mitra Pengadaan MR',
            'phone' => '081234567888',
        ]);

        $this->centralWarehouse = Warehouse::where('is_central', true)->first() ?? Warehouse::create([
            'name' => 'Gudang Sentral Multi Test',
            'code' => 'WH-SNT-MR',
            'is_central' => true,
        ]);

        $this->projectWarehouse = Warehouse::where('is_central', false)->first() ?? Warehouse::create([
            'name' => 'Gudang Proyek Multi Test',
            'code' => 'WH-PRY-MR',
            'is_central' => false,
        ]);

        $category = Category::where('type', 'material')->first();
        $unit = Unit::first();
        $this->material = Material::firstOrCreate(
            ['sku' => 'MAT-MR-PO-01'],
            [
                'name' => 'Pipa PVC 3 inch',
                'category_id' => $category->id,
                'unit_id' => $unit->id,
                'min_stock_central' => 10,
            ]
        );
    }

    public function test_mr_multi_po_flow_quota_validation_and_cancellation(): void
    {
        // 1. Buat MR 100 batang pipa dengan fulfillment 'purchase' (status approved)
        $mr = MaterialRequest::create([
            'request_number' => 'MR-2026-MULTI-PO',
            'from_warehouse_id' => $this->centralWarehouse->id,
            'to_warehouse_id' => $this->projectWarehouse->id,
            'requested_by_user_id' => $this->adminPusat->id,
            'status' => 'approved',
            'approved_by_user_id' => $this->adminPusat->id,
            'approved_at' => now(),
        ]);

        $mrItemPurchase = MaterialRequestItem::create([
            'material_request_id' => $mr->id,
            'material_id' => $this->material->id,
            'qty_requested' => 100,
            'qty_approved' => 100,
            'qty_ordered' => 0,
            'fulfillment' => 'purchase',
        ]);

        // 2. Buat PO 1 untuk 60 batang pipa
        $po1Data = [
            'supplier_id' => $this->supplier->id,
            'order_date' => now()->toDateString(),
            'items' => [
                [
                    'material_id' => $this->material->id,
                    'material_request_item_id' => $mrItemPurchase->id,
                    'quantity' => 60,
                    'unit_price' => 75000,
                ],
            ],
        ];

        $res1 = $this->actingAs($this->adminPO)
            ->post(route('purchase-orders.store'), $po1Data);

        $res1->assertSessionHasNoErrors();
        $po1 = PurchaseOrder::latest('id')->first();
        $this->assertNotNull($po1);

        // Kuota terpesan bertambah menjadi 60
        $this->assertEquals(60, (float) $mrItemPurchase->fresh()->qty_ordered);

        // 3. Coba buat PO 2 untuk 50 batang pipa (sisa kuota hanya 40) -> HARUS DITOLAK
        $po2Invalid = [
            'supplier_id' => $this->supplier->id,
            'order_date' => now()->toDateString(),
            'items' => [
                [
                    'material_id' => $this->material->id,
                    'material_request_item_id' => $mrItemPurchase->id,
                    'quantity' => 50, // Melebihi kuota 40!
                    'unit_price' => 75000,
                ],
            ],
        ];

        $resInvalid = $this->actingAs($this->adminPO)
            ->post(route('purchase-orders.store'), $po2Invalid);

        $resInvalid->assertSessionHasErrors();
        $this->assertEquals(60, (float) $mrItemPurchase->fresh()->qty_ordered);

        // 4. Buat PO 2 untuk 40 batang pipa (pas kuota tersisa) -> HARUS SUKSES
        $po2Valid = [
            'supplier_id' => $this->supplier->id,
            'order_date' => now()->toDateString(),
            'items' => [
                [
                    'material_id' => $this->material->id,
                    'material_request_item_id' => $mrItemPurchase->id,
                    'quantity' => 40,
                    'unit_price' => 75000,
                ],
            ],
        ];

        $res2 = $this->actingAs($this->adminPO)
            ->post(route('purchase-orders.store'), $po2Valid);

        $res2->assertSessionHasNoErrors();
        $this->assertEquals(100, (float) $mrItemPurchase->fresh()->qty_ordered);

        // 5. Batalkan (cancel) PO 1 (60 batang)
        $cancelRes = $this->actingAs($this->adminPusat)
            ->post(route('purchase-orders.cancel', $po1));

        $cancelRes->assertSessionHas('success');
        $this->assertEquals('cancelled', $po1->fresh()->status);

        // Kuota pada MR item kembali berkurang 60 menjadi 40
        $this->assertEquals(40, (float) $mrItemPurchase->fresh()->qty_ordered);
    }

    public function test_cannot_create_po_for_stock_fulfillment_mr_item(): void
    {
        $mr = MaterialRequest::create([
            'request_number' => 'MR-2026-STOCK-ONLY',
            'from_warehouse_id' => $this->centralWarehouse->id,
            'to_warehouse_id' => $this->projectWarehouse->id,
            'requested_by_user_id' => $this->adminPusat->id,
            'status' => 'approved',
            'approved_by_user_id' => $this->adminPusat->id,
            'approved_at' => now(),
        ]);

        $mrItemStock = MaterialRequestItem::create([
            'material_request_id' => $mr->id,
            'material_id' => $this->material->id,
            'qty_requested' => 50,
            'qty_approved' => 50,
            'qty_ordered' => 0,
            'fulfillment' => 'stock', // Pemenuhan dari stok gudang!
        ]);

        $poStockItemData = [
            'supplier_id' => $this->supplier->id,
            'order_date' => now()->toDateString(),
            'items' => [
                [
                    'material_id' => $this->material->id,
                    'material_request_item_id' => $mrItemStock->id,
                    'quantity' => 10,
                    'unit_price' => 75000,
                ],
            ],
        ];

        $response = $this->actingAs($this->adminPO)
            ->post(route('purchase-orders.store'), $poStockItemData);

        // Harus ditolak karena item dialokasikan untuk stock transfer (Surat Jalan), bukan PO
        $response->assertSessionHasErrors();
        $this->assertEquals(0, (float) $mrItemStock->fresh()->qty_ordered);
    }
}
