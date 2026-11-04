<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\GoodsReceipt;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoodsReceiptStandaloneControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $gudangUser;
    protected Warehouse $warehouse;
    protected Supplier $supplier;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);

        $this->warehouse = Warehouse::firstOrCreate(['code' => 'W-PRJ-001'], ['name' => 'Gudang Site FK Teknik', 'is_central' => false]);
        $this->supplier = Supplier::firstOrCreate(['code' => 'SUP-TB-01'], ['name' => 'TB Sumber Bangunan']);
        $this->category = Category::firstOrCreate(['code' => 'CAT-ARSITEK'], ['name' => 'ARSITEK', 'type' => 'material']);

        $this->gudangUser = User::factory()->create(['email_verified_at' => now()]);
        $this->gudangUser->assignRole('Admin Gudang Proyek');
        $this->gudangUser->warehouses()->attach($this->warehouse->id);
    }

    public function test_can_store_and_confirm_goods_receipt_directly_without_po(): void
    {
        $response = $this->actingAs($this->gudangUser)->post(route('goods-receipts.store'), [
            'warehouse_id'    => $this->warehouse->id,
            'supplier_id'     => $this->supplier->id,
            'received_at'     => now()->toDateString(),
            'invoice_number'  => 'DO-VENDOR-404',
            'notes'           => 'Penerimaan keramik lantai langsung',
            'items' => [
                [
                    'item_type'     => 'material',
                    'name'          => 'Keramik Granit 60x60 Putih',
                    'category_id'   => $this->category->id,
                    'custom_unit'   => 'dus',
                    'qty_received'  => 50,
                    'condition'     => 'good',
                ]
            ]
        ]);

        $response->assertSessionHasNoErrors();
        $gr = GoodsReceipt::latest('id')->first();
        $this->assertNotNull($gr);
        $this->assertEquals('DO-VENDOR-404', $gr->invoice_number);
        $this->assertNull($gr->purchase_order_id);
    }

    public function test_goods_receipt_create_view_renders_direct_item_inputs_without_mandatory_po_select(): void
    {
        $response = $this->actingAs($this->gudangUser)->get(route('goods-receipts.create'));
        $response->assertOk();
        $response->assertSee('Penerimaan Barang Langsung');
        $response->assertSee('Tambah Baris Barang');
        $response->assertDontSee('Pilih Purchase Order (Wajib)');
    }
}
