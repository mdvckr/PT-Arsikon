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
use Database\Seeders\MasterDataSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseOrderPriceLockTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminPusat;
    protected User $adminGudang;
    protected Supplier $supplier;
    protected Warehouse $warehouse;
    protected Material $material;
    protected PurchaseOrder $poDraft;
    protected PurchaseOrderItem $poItemDraft;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(MasterDataSeeder::class);

        $this->adminPusat = User::firstOrCreate(
            ['email' => 'admin.pusat.t9@example.com'],
            ['name' => 'Admin Pusat T9', 'password' => bcrypt('password')]
        );
        $this->adminPusat->syncRoles(['Admin Pusat']);

        $this->adminGudang = User::firstOrCreate(
            ['email' => 'admin.gudang.t9@example.com'],
            ['name' => 'Admin Gudang T9', 'password' => bcrypt('password')]
        );
        $this->adminGudang->syncRoles(['Admin Gudang Pusat']);

        $this->supplier = Supplier::first() ?? Supplier::create([
            'name' => 'PT Supplier T9',
            'phone' => '081234567811',
        ]);

        $this->warehouse = Warehouse::first() ?? Warehouse::create([
            'name' => 'Gudang Pusat T9',
            'code' => 'WH-PST-T9',
            'is_central' => true,
        ]);

        $category = Category::where('type', 'material')->first();
        $unit = Unit::first();

        $this->material = Material::firstOrCreate(
            ['sku' => 'MAT-T9-01'],
            [
                'name' => 'Batu Bata T9',
                'category_id' => $category->id,
                'unit_id' => $unit->id,
            ]
        );

        $this->poDraft = PurchaseOrder::create([
            'supplier_id' => $this->supplier->id,
            'delivery_warehouse_id' => $this->warehouse->id,
            'created_by' => $this->adminPusat->id,
            'status' => 'draft',
            'order_date' => now()->toDateString(),
            'total_amount' => 500000,
        ]);

        $this->poItemDraft = PurchaseOrderItem::create([
            'purchase_order_id' => $this->poDraft->id,
            'material_id' => $this->material->id,
            'quantity' => 10,
            'unit_price' => 50000,
            'subtotal' => 500000,
        ]);
    }

    public function test_unit_price_can_be_updated_when_po_is_draft()
    {
        $this->poItemDraft->unit_price = 55000;
        $this->poItemDraft->save();

        $this->assertEquals(55000, (float) $this->poItemDraft->fresh()->unit_price);
    }

    public function test_unit_price_cannot_be_updated_when_po_is_not_draft()
    {
        $this->poDraft->update(['status' => 'approved']);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('tidak dapat diubah');

        $this->poItemDraft->unit_price = 70000;
        $this->poItemDraft->save();
    }

    public function test_other_fields_can_still_be_updated_when_po_is_not_draft()
    {
        $this->poDraft->update(['status' => 'approved']);

        // Mengubah field selain unit_price (misal received_qty) tidak boleh terhalang exception harga
        $this->poItemDraft->received_qty = 5;
        $this->poItemDraft->save();

        $this->assertEquals(5, (float) $this->poItemDraft->fresh()->received_qty);
        $this->assertEquals(50000, (float) $this->poItemDraft->fresh()->unit_price);
    }

    public function test_goods_receipt_copies_unit_price_from_po_item_and_ignores_client_request()
    {
        $this->poDraft->update(['status' => 'sent']);

        $service = app(GoodsReceiptService::class);

        $data = [
            'purchase_order_id' => $this->poDraft->id,
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'received_at' => now()->toDateString(),
            'items' => [
                [
                    'material_id' => $this->material->id,
                    'purchase_order_item_id' => $this->poItemDraft->id,
                    'quantity' => 5,
                    'unit_price' => 999999, // Nilai manipulatif dari klien
                ]
            ],
        ];

        $gr = $service->create($data, $this->adminGudang->id);

        $grItem = $gr->items()->first();
        // unit_price wajib disalin dari PO item (50000), BUKAN 999999
        $this->assertEquals(50000, (float) $grItem->unit_price);
    }

    public function test_goods_receipt_without_po_can_use_manual_unit_price()
    {
        $service = app(GoodsReceiptService::class);

        $data = [
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'received_at' => now()->toDateString(),
            'items' => [
                [
                    'material_id' => $this->material->id,
                    'purchase_order_item_id' => null,
                    'quantity' => 10,
                    'unit_price' => 35000,
                ]
            ],
        ];

        $gr = $service->create($data, $this->adminGudang->id);

        $grItem = $gr->items()->first();
        $this->assertEquals(35000, (float) $grItem->unit_price);
    }
}
