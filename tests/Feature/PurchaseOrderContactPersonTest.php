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

class PurchaseOrderContactPersonTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminPO;
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

        $this->adminPO = User::firstOrCreate(
            ['email' => 'admin.po.test@example.com'],
            ['name' => 'Admin Pengadaan PO', 'password' => bcrypt('password')]
        );
        $this->adminPO->syncRoles(['Admin PO']);

        $this->supplier = Supplier::first() ?? Supplier::create([
            'name' => 'PT Mitra Pengadaan CP',
            'phone' => '081234567899',
        ]);
        $this->warehouse = Warehouse::where('is_central', true)->first() ?? Warehouse::create([
            'name' => 'Gudang Pusat CP',
            'code' => 'WH-PST-CP',
            'is_central' => true,
        ]);
        $category = \App\Models\Category::firstOrCreate(
            ['code' => 'CAT-SMN'],
            ['name' => 'Semen', 'type' => 'material']
        );
        $unit = \App\Models\Unit::firstOrCreate(
            ['code' => 'ZAK'],
            ['name' => 'Zak', 'is_decimal' => false]
        );
        $this->material = Material::firstOrCreate(
            ['sku' => 'MAT-CP-01'],
            [
                'name' => 'Semen Gresik 40kg',
                'category_id' => $category->id,
                'unit_id' => $unit->id,
            ]
        );

        // Siapkan MR yang disetujui untuk jalur pembelian vendor
        $this->mr = MaterialRequest::create([
            'request_number'       => 'MR-TEST-CP-001',
            'requested_by_user_id' => $this->adminPO->id,
            'to_warehouse_id'      => $this->warehouse->id,
            'status'               => 'approved',
            'notes'                => 'MR Test CP',
        ]);

        $this->mrItem = MaterialRequestItem::create([
            'material_request_id' => $this->mr->id,
            'material_id'         => $this->material->id,
            'qty_requested'       => 50,
            'qty_approved'        => 50,
            'qty_ordered'         => 0,
            'fulfillment'         => 'purchase',
        ]);
    }

    public function test_create_po_page_displays_contact_person_input(): void
    {
        $response = $this->actingAs($this->adminPO)->get(route('purchase-orders.create'));

        $response->assertStatus(200);
        $response->assertSee('Contact Person (CP) Print');
        $response->assertSee('contact_person');
        $response->assertSee($this->adminPO->name);
    }

    public function test_can_create_po_with_custom_contact_person(): void
    {
        $response = $this->actingAs($this->adminPO)->post(route('purchase-orders.store'), [
            'supplier_id'           => $this->supplier->id,
            'material_request_id'   => $this->mr->id,
            'delivery_warehouse_id' => $this->warehouse->id,
            'order_date'            => now()->toDateString(),
            'contact_person'        => 'Bpk. Hendra Kurniawan (0812-9988-7766)',
            'notes'                 => 'Kirim segera',
            'items'                 => [
                [
                    'material_id'              => $this->material->id,
                    'material_request_item_id' => $this->mrItem->id,
                    'quantity'                 => 10,
                    'unit_price'               => 15000,
                ],
            ],
        ]);

        $response->assertSessionHasNoErrors();
        $po = PurchaseOrder::where('material_request_id', $this->mr->id)->latest('id')->first();
        $this->assertNotNull($po);
        $this->assertEquals('Bpk. Hendra Kurniawan (0812-9988-7766)', $po->contact_person);

        // Periksa tampilan show
        $showResponse = $this->actingAs($this->adminPO)->get(route('purchase-orders.show', $po));
        $showResponse->assertStatus(200);
        $showResponse->assertSee('Contact Person (CP):');
        $showResponse->assertSee('Bpk. Hendra Kurniawan (0812-9988-7766)');

        // Periksa tampilan print PDF
        $printResponse = $this->actingAs($this->adminPO)->get(route('purchase-orders.print', $po));
        $printResponse->assertStatus(200);
        $printResponse->assertSee('CP : Bpk. Hendra Kurniawan (0812-9988-7766)');
    }

    public function test_print_po_falls_back_to_creator_name_when_cp_is_empty(): void
    {
        $po = PurchaseOrder::create([
            'supplier_id'           => $this->supplier->id,
            'supplier_name'         => $this->supplier->name,
            'material_request_id'   => $this->mr->id,
            'delivery_warehouse_id' => $this->warehouse->id,
            'risk_bearer'           => 'vendor',
            'created_by'            => $this->adminPO->id,
            'status'                => 'draft',
            'order_date'            => now()->toDateString(),
            'contact_person'        => null,
        ]);

        $printResponse = $this->actingAs($this->adminPO)->get(route('purchase-orders.print', $po));
        $printResponse->assertStatus(200);
        $printResponse->assertSee('CP : ' . $this->adminPO->name);
    }
}
