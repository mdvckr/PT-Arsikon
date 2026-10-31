<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Material;
use App\Models\ProcurementRequest;
use App\Models\ProcurementRequestItem;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use Database\Seeders\MasterDataSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProcurementMultiPoTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminPusat;
    protected User $adminPO;
    protected Supplier $supplier;
    protected Material $material;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(MasterDataSeeder::class);

        $this->adminPusat = User::firstOrCreate(
            ['email' => 'admin.multi@example.com'],
            ['name' => 'Admin Multi PO', 'password' => bcrypt('password')]
        );
        $this->adminPusat->syncRoles(['Admin Pusat']);

        $this->adminPO = User::firstOrCreate(
            ['email' => 'adminpo.multi@example.com'],
            ['name' => 'Admin PO Multi', 'password' => bcrypt('password')]
        );
        $this->adminPO->syncRoles(['Admin PO']);

        $this->supplier = Supplier::first() ?? Supplier::create([
            'name' => 'PT Semen Perkasa',
            'phone' => '081234567899',
        ]);

        $category = Category::where('type', 'material')->first();
        $unit = Unit::first();
        $this->material = Material::firstOrCreate(
            ['sku' => 'MAT-SMN-MULTI'],
            [
                'name' => 'Semen Gresik 50kg',
                'category_id' => $category->id,
                'unit_id' => $unit->id,
                'min_stock_central' => 10,
            ]
        );
    }

    public function test_pr_multi_po_flow_quota_validation_and_cancellation(): void
    {
        // 1. Buat PR 100 sak semen (status approved)
        $pr = ProcurementRequest::create([
            'pr_number' => 'PR-2026-MULTI01',
            'requested_by' => $this->adminPusat->id,
            'status' => 'approved',
            'needed_by' => now()->addDays(5)->toDateString(),
        ]);

        $prItem = ProcurementRequestItem::create([
            'procurement_request_id' => $pr->id,
            'material_id' => $this->material->id,
            'quantity' => 100,
            'qty_ordered' => 0,
            'estimated_price' => 50000,
        ]);

        // 2. Buat PO 1 untuk 60 sak (dari kuota 100)
        $po1Data = [
            'supplier_id' => $this->supplier->id,
            'procurement_request_id' => $pr->id,
            'order_date' => now()->toDateString(),
            'items' => [
                [
                    'material_id' => $this->material->id,
                    'procurement_request_item_id' => $prItem->id,
                    'quantity' => 60,
                    'unit_price' => 50000,
                ],
            ],
        ];

        $response1 = $this->actingAs($this->adminPO)
            ->post(route('purchase-orders.store'), $po1Data);

        $response1->assertSessionHasNoErrors();
        $po1 = PurchaseOrder::where('procurement_request_id', $pr->id)->latest('id')->first();
        $this->assertNotNull($po1);

        // Verifikasi PR item qty_ordered = 60 dan status PR = partially_ordered
        $this->assertEquals(60, (float) $prItem->fresh()->qty_ordered);
        $this->assertEquals('partially_ordered', $pr->fresh()->status);

        // 3. Coba buat PO 2 untuk 50 sak (melebihi sisa kuota 40) -> HARUS GAGAL
        $po2InvalidData = [
            'supplier_id' => $this->supplier->id,
            'procurement_request_id' => $pr->id,
            'order_date' => now()->toDateString(),
            'items' => [
                [
                    'material_id' => $this->material->id,
                    'procurement_request_item_id' => $prItem->id,
                    'quantity' => 50, // Kuota tersisa hanya 40!
                    'unit_price' => 50000,
                ],
            ],
        ];

        $responseInvalid = $this->actingAs($this->adminPO)
            ->post(route('purchase-orders.store'), $po2InvalidData);

        $responseInvalid->assertSessionHasErrors();
        // Pastikan kuota tidak bertambah
        $this->assertEquals(60, (float) $prItem->fresh()->qty_ordered);
        $this->assertEquals('partially_ordered', $pr->fresh()->status);

        // 4. Buat PO 2 untuk 40 sak (pas kuota tersisa) -> HARUS SUKSES
        $po2ValidData = [
            'supplier_id' => $this->supplier->id,
            'procurement_request_id' => $pr->id,
            'order_date' => now()->toDateString(),
            'items' => [
                [
                    'material_id' => $this->material->id,
                    'procurement_request_item_id' => $prItem->id,
                    'quantity' => 40,
                    'unit_price' => 50000,
                ],
            ],
        ];

        $response2 = $this->actingAs($this->adminPO)
            ->post(route('purchase-orders.store'), $po2ValidData);

        $response2->assertSessionHasNoErrors();
        $po2 = PurchaseOrder::where('id', '!=', $po1->id)
            ->where('procurement_request_id', $pr->id)
            ->first();
        $this->assertNotNull($po2);

        // Verifikasi PR item qty_ordered = 100 dan status PR = fully_ordered
        $this->assertEquals(100, (float) $prItem->fresh()->qty_ordered);
        $this->assertEquals('fully_ordered', $pr->fresh()->status);

        // 5. Batalkan (cancel) PO 1 (60 sak)
        $this->flushSession();
        $cancelResponse = $this->actingAs($this->adminPusat)
            ->post(route('purchase-orders.cancel', $po1));

        $cancelResponse->assertSessionHas('success');
        $this->assertEquals('cancelled', $po1->fresh()->status);

        // Verifikasi PR item qty_ordered berkurang 60 menjadi 40
        $this->assertEquals(40, (float) $prItem->fresh()->qty_ordered);

        // Status PR kembali menjadi partially_ordered
        $this->assertEquals('partially_ordered', $pr->fresh()->status);
    }
}
