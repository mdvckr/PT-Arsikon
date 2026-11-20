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
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PurchaseOrderAttachmentTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminPO;
    protected Supplier $supplier;
    protected Warehouse $warehouse;
    protected Material $material;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(MasterDataSeeder::class);

        $this->adminPO = User::firstOrCreate(
            ['email' => 'admin.po.attach@example.com'],
            ['name' => 'Admin Pengadaan Attachment', 'password' => bcrypt('password')]
        );
        $this->adminPO->syncRoles(['Admin Pengadaan']);

        $this->supplier = Supplier::first() ?? Supplier::create([
            'code'         => 'SUP-TEST-01',
            'name'         => 'Supplier Utama Test',
            'phone'        => '081234567890',
            'address'      => 'Jl. Test No. 1',
            'is_permanent' => true,
        ]);

        $this->warehouse = Warehouse::where('is_central', true)->first() ?? Warehouse::create([
            'code'       => 'GDG-PUSAT-01',
            'name'       => 'Gudang Pusat',
            'is_central' => true,
        ]);

        $this->adminPO->warehouses()->syncWithoutDetaching([$this->warehouse->id]);

        $category = \App\Models\Category::firstOrCreate(['code' => 'CAT-BB-01'], ['name' => 'Bahan Baku', 'type' => 'material']);
        $unit = \App\Models\Unit::firstOrCreate(['code' => 'UNT-SAK-01'], ['name' => 'Sak']);
        $this->material = Material::first() ?? Material::create([
            'category_id' => $category->id,
            'unit_id'     => $unit->id,
            'sku'         => 'MAT-PO-01',
            'name'        => 'Semen Holcim',
            'type'        => 'Semen',
        ]);

        $this->mr = MaterialRequest::create([
            'request_number'       => 'MR-TEST-ATT-001',
            'from_warehouse_id'    => $this->warehouse->id,
            'to_warehouse_id'      => $this->warehouse->id,
            'requested_by_user_id' => $this->adminPO->id,
            'status'               => 'approved',
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

    public function test_can_upload_photo_attachment_when_creating_purchase_order(): void
    {
        Storage::fake('private');

        $file = UploadedFile::fake()->image('bukti_po.jpg', 800, 600);

        $response = $this->actingAs($this->adminPO)->post(route('purchase-orders.store'), [
            'supplier_id'           => $this->supplier->id,
            'delivery_warehouse_id' => $this->warehouse->id,
            'order_date'            => now()->toDateString(),
            'risk_bearer'           => 'buyer',
            'attachment'            => $file,
            'items'                 => [
                [
                    'material_id'              => $this->material->id,
                    'material_request_item_id' => $this->mrItem->id,
                    'quantity'                 => 10,
                    'unit_price'               => 50000,
                ],
            ],
        ]);

        $response->assertSessionHasNoErrors();
        $po = PurchaseOrder::latest('id')->first();
        $this->assertNotNull($po);
        $this->assertNotNull($po->attachment_path);
        Storage::disk('private')->assertExists($po->attachment_path);
    }

    public function test_authorized_user_can_view_purchase_order_attachment(): void
    {
        Storage::fake('private');

        $storedPath = Storage::disk('private')->putFile('purchase_orders', UploadedFile::fake()->image('po_doc.jpg'));
        $po = PurchaseOrder::create([
            'po_number'             => 'PO-TEST-ATT-001',
            'supplier_id'           => $this->supplier->id,
            'delivery_warehouse_id' => $this->warehouse->id,
            'created_by'            => $this->adminPO->id,
            'status'                => 'draft',
            'order_date'            => now()->toDateString(),
            'total_amount'          => 100000,
            'attachment_path'       => $storedPath,
        ]);

        $response = $this->actingAs($this->adminPO)->get(route('purchase-orders.file', $po));
        $response->assertOk();
    }

    public function test_unauthenticated_user_cannot_access_purchase_order_file(): void
    {
        $po = PurchaseOrder::create([
            'po_number'             => 'PO-TEST-ATT-002',
            'supplier_id'           => $this->supplier->id,
            'delivery_warehouse_id' => $this->warehouse->id,
            'created_by'            => $this->adminPO->id,
            'status'                => 'draft',
            'order_date'            => now()->toDateString(),
            'total_amount'          => 100000,
            'attachment_path'       => 'purchase_orders/dummy.jpg',
        ]);

        $response = $this->get(route('purchase-orders.file', $po));
        $response->assertRedirectContains('/login');
    }
}
