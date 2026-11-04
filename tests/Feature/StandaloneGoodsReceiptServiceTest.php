<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\GoodsReceipt;
use App\Models\Inventory;
use App\Models\Material;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\GoodsReceiptService;
use App\Services\StockService;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StandaloneGoodsReceiptServiceTest extends TestCase
{
    use RefreshDatabase;

    protected GoodsReceiptService $service;
    protected User $gudangUser;
    protected Warehouse $warehouse;
    protected Supplier $supplier;
    protected Category $category;
    protected Unit $unit;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        $this->service = app(GoodsReceiptService::class);

        $this->warehouse = Warehouse::firstOrCreate(['code' => 'W-TEST-SITE'], ['name' => 'Gudang Site Proyek Test', 'is_central' => false]);
        $this->supplier = Supplier::firstOrCreate(['code' => 'SUP-TEST-01'], ['name' => 'Supplier Semen Mandiri']);
        $this->category = Category::firstOrCreate(['code' => 'CAT-STRUKTUR'], ['name' => 'STRUKTUR', 'type' => 'material']);
        $this->unit = Unit::firstOrCreate(['code' => 'SAK'], ['name' => 'Sak']);

        $this->gudangUser = User::factory()->create(['email_verified_at' => now()]);
        $this->gudangUser->assignRole('Admin Gudang Proyek');
        $this->gudangUser->warehouses()->attach($this->warehouse->id);
    }

    public function test_goods_receipt_without_po_appends_incoming_stage_and_increases_stock_for_existing_material(): void
    {
        // 1. Buat material eksisting
        $existingMaterial = Material::create([
            'name' => 'Semen Tiga Roda 50kg',
            'sku' => 'MAT-STR-001',
            'category_id' => $this->category->id,
            'unit_id' => $this->unit->id,
            'incoming_stages' => [],
        ]);

        // 2. Buat Goods Receipt langsung tanpa PO
        $payload = [
            'purchase_order_id'   => null,
            'material_request_id' => null,
            'supplier_id'         => $this->supplier->id,
            'supplier_name'       => $this->supplier->name,
            'warehouse_id'        => $this->warehouse->id,
            'received_at'         => now()->toDateString(),
            'invoice_number'      => 'SJ-VENDOR-999',
            'notes'               => 'Pengiriman material semen langsung dari pabrik',
            'items' => [
                [
                    'item_type'     => 'material',
                    'material_id'   => $existingMaterial->id,
                    'name'          => 'Semen Tiga Roda 50kg',
                    'category_id'   => $this->category->id,
                    'qty_shipped'   => 100,
                    'qty_received'  => 100,
                    'condition'     => 'good',
                    'notes'         => 'Cor lantai 1',
                ]
            ]
        ];

        $gr = $this->service->create($payload, $this->gudangUser->id);
        $this->assertNull($gr->purchase_order_id);
        $this->assertEquals('draft', $gr->status);

        // 3. Konfirmasi GR
        $confirmedGr = $this->service->confirm($gr, $this->gudangUser->id);
        $this->assertEquals('confirmed', $confirmedGr->status);

        // 4. Periksa stok bertambah di gudang tujuan
        $inv = Inventory::where('warehouse_id', $this->warehouse->id)
            ->where('material_id', $existingMaterial->id)
            ->first();
        $this->assertNotNull($inv);
        $this->assertEquals(100, (float) $inv->quantity);

        // 5. Periksa incoming_stages tercatat di material
        $existingMaterial->refresh();
        $stages = $existingMaterial->incoming_stages ?? [];
        $this->assertNotEmpty($stages);
        $lastStage = end($stages);
        $this->assertEquals($gr->receipt_number, $lastStage['receipt_number'] ?? null);
        $this->assertEquals(100, (float) ($lastStage['qty'] ?? 0));
        $this->assertEquals('received', $lastStage['status'] ?? null);
    }

    public function test_goods_receipt_without_po_auto_registers_new_material_when_not_exists(): void
    {
        $payload = [
            'purchase_order_id'   => null,
            'supplier_id'         => $this->supplier->id,
            'warehouse_id'        => $this->warehouse->id,
            'received_at'         => now()->toDateString(),
            'invoice_number'      => 'SJ-BARU-001',
            'items' => [
                [
                    'item_type'     => 'material',
                    'material_id'   => null,
                    'name'          => 'Bata Ringan Hebel 10cm',
                    'category_id'   => $this->category->id,
                    'custom_type'   => 'Hebel SNI',
                    'custom_unit'   => 'm3',
                    'qty_shipped'   => 25,
                    'qty_received'  => 25,
                    'condition'     => 'good',
                ]
            ]
        ];

        $gr = $this->service->create($payload, $this->gudangUser->id);
        $this->service->confirm($gr, $this->gudangUser->id);

        $newMaterial = Material::whereRaw('LOWER(name) = ?', ['bata ringan hebel 10cm'])->first();
        $this->assertNotNull($newMaterial);
        $this->assertEquals(25, (float) $newMaterial->inventories->first()->quantity);
        $stages = $newMaterial->incoming_stages ?? [];
        $this->assertNotEmpty($stages);
    }
}
