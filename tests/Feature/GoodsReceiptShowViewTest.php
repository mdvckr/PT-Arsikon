<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\Material;
use App\Models\Project;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GoodsReceiptShowViewTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Project $project;
    protected Warehouse $warehouse;
    protected Supplier $supplier;
    protected GoodsReceipt $goodsReceipt;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'Super Admin']);
        Permission::firstOrCreate(['name' => 'view goods receipts']);
        Permission::firstOrCreate(['name' => 'confirm goods receipts']);

        $this->user = User::factory()->create();
        $this->user->assignRole('Super Admin');
        $this->user->givePermissionTo(['view goods receipts', 'confirm goods receipts']);

        $this->project = Project::create([
            'name' => 'Proyek Gedung A',
            'code' => 'PRJ-A',
            'status' => 'active',
        ]);

        $this->warehouse = Warehouse::create([
            'name' => 'Gudang Proyek FK Teknik',
            'code' => 'GDG-FK',
            'type' => 'project',
            'project_id' => $this->project->id,
            'is_active' => true,
        ]);

        $this->user->warehouses()->attach($this->warehouse->id);

        $this->supplier = Supplier::create([
            'name' => 'Toko Cat Lancar',
            'phone' => '08123456789',
        ]);

        $po = PurchaseOrder::create([
            'po_number' => 'PO-2026-0001',
            'supplier_id' => $this->supplier->id,
            'delivery_warehouse_id' => $this->warehouse->id,
            'created_by' => $this->user->id,
            'order_date' => Carbon::now()->format('Y-m-d'),
            'status' => 'approved',
            'total_amount' => 1500000,
        ]);

        $this->goodsReceipt = GoodsReceipt::create([
            'receipt_number' => 'GR-20261114-0001',
            'warehouse_id' => $this->warehouse->id,
            'supplier_id' => $this->supplier->id,
            'purchase_order_id' => $po->id,
            'received_by_user_id' => $this->user->id,
            'received_by_name' => $this->user->name,
            'created_by' => $this->user->id,
            'receipt_date' => Carbon::now()->format('Y-m-d'),
            'status' => 'draft',
            'notes' => 'Catatan kiriman pertama barang alat.',
        ]);

        $cat = Category::create(['name' => 'Alat Teknik', 'code' => 'AT']);
        $unit = Unit::create(['name' => 'Unit', 'code' => 'unit']);
        $mat = Material::create([
            'name' => 'Borr Listrik Bosch 13mm',
            'sku' => 'MAT-BOR-01',
            'category_id' => $cat->id,
            'unit_id' => $unit->id,
        ]);

        GoodsReceiptItem::create([
            'goods_receipt_id' => $this->goodsReceipt->id,
            'material_id' => $mat->id,
            'item_type' => 'material',
            'qty_shipped' => 10,
            'qty_received' => 10,
            'qty_damaged' => 0,
            'qty_missing' => 0,
        ]);
    }

    public function test_goods_receipt_show_renders_clean_layout_without_duplication(): void
    {
        $response = $this->actingAs($this->user)->get(route('goods-receipts.show', $this->goodsReceipt));

        $response->assertOk();

        // 1. Memastikan nomor tanda terima dan status tampil jelas di header
        $response->assertSee('GR-20261114-0001');
        $response->assertSee('Draft (Menunggu Konfirmasi)');

        // 2. Memastikan metadata card tampil (Supplier, Gudang, Dokumen Referensi)
        $response->assertSee('Toko Cat Lancar');
        $response->assertSee('Gudang Proyek FK Teknik');
        $response->assertSee('PO-2026-0001');

        // 3. Memastikan rincian item barang tampil
        $response->assertSee('Borr Listrik Bosch 13mm');
        $response->assertSee('10');

        // 4. Memastikan tombol konfirmasi ada dan teks konfirmasi
        $response->assertSee('Konfirmasi &amp; Update Stok', false);

        // 5. Verifikasi bahwa tombol konfirmasi hanya muncul 1 KALI (tidak terduplikasi seperti sebelumnya)
        $content = $response->getContent();
        $confirmButtonCount = substr_count($content, 'Konfirmasi &amp; Update Stok');
        $this->assertEquals(1, $confirmButtonCount, 'Tombol Konfirmasi hanya boleh ada 1 buah dan tidak terduplikasi.');
    }
}
