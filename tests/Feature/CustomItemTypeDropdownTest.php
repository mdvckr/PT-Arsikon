<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\GoodsReceipt;
use App\Models\MaterialRequest;
use App\Models\MaterialRequestItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\Tool;
use App\Models\ToolInventory;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\GoodsReceiptService;
use Database\Seeders\MasterDataSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomItemTypeDropdownTest extends TestCase
{
    use RefreshDatabase;

    protected User $karyawan;
    protected User $adminPusat;
    protected Warehouse $projectWarehouse;
    protected Warehouse $centralWarehouse;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(MasterDataSeeder::class);

        $this->centralWarehouse = Warehouse::where('is_central', true)->first() ?? Warehouse::create([
            'name'       => 'Gudang Pusat Test',
            'code'       => 'WH-PST-TEST',
            'is_central' => true,
        ]);

        $this->projectWarehouse = Warehouse::where('is_central', false)->first() ?? Warehouse::create([
            'name'       => 'Gudang Proyek Test',
            'code'       => 'WH-PRY-TEST',
            'is_central' => false,
        ]);

        $this->karyawan = User::firstOrCreate(
            ['email' => 'karyawan.custom.test@example.com'],
            ['name' => 'Karyawan Test', 'password' => bcrypt('password')]
        );
        $this->karyawan->syncRoles(['Karyawan']);
        $this->karyawan->warehouses()->syncWithoutDetaching([$this->projectWarehouse->id]);

        $this->adminPusat = User::firstOrCreate(
            ['email' => 'admin.pusat.custom.test@example.com'],
            ['name' => 'Admin Pusat Custom Test', 'password' => bcrypt('password')]
        );
        $this->adminPusat->syncRoles(['Admin Pusat']);
    }

    public function test_create_and_edit_forms_render_item_dropdown_for_custom_items(): void
    {
        $response = $this->actingAs($this->karyawan)->get(route('material-requests.create', [
            'warehouse_id' => $this->projectWarehouse->id,
        ]));

        $response->assertStatus(200);
        $response->assertSee('Item');
        $response->assertSee('Material</option>', false);
        $response->assertSee('Alat</option>', false);
        $response->assertSee('custom-item-type-select');
    }

    public function test_can_submit_custom_item_with_tool_type_and_integer_qty(): void
    {
        $toolCategory = Category::where('type', 'tool')->first();

        $payload = [
            'warehouse_id'        => $this->projectWarehouse->id,
            'source_warehouse_id' => null, // Pengadaan
            'requested_as'        => 'Admin Proyek',
            'notes'               => 'Permintaan alat custom lapangan',
            'action'              => 'submit',
            'custom_items'        => [
                [
                    'name'        => 'Genset Mini Portable 1500W',
                    'item_type'   => 'tool',
                    'spec'        => 'Bensin 4-tak',
                    'category_id' => $toolCategory?->id,
                    'unit'        => 'unit',
                    'qty'         => 2,
                    'location'    => 'Area Direksi Keet',
                    'notes'       => 'Kebutuhan darurat listrik',
                ],
                [
                    'name'        => 'Triplek 12mm Custom',
                    'item_type'   => 'material',
                    'spec'        => '120x240 cm',
                    'category_id' => null,
                    'unit'        => 'lembar',
                    'qty'         => 15.5,
                    'location'    => 'Lantai 1',
                    'notes'       => 'Bekisting',
                ]
            ],
        ];

        $response = $this->actingAs($this->karyawan)->post(route('material-requests.store'), $payload);
        $response->assertSessionHasNoErrors();

        $mr = MaterialRequest::where('notes', 'Permintaan alat custom lapangan')->first();
        $this->assertNotNull($mr);
        $response->assertRedirect(route('material-requests.show', $mr));

        $toolItem = $mr->items()->where('custom_item_name', 'Genset Mini Portable 1500W')->first();
        $this->assertNotNull($toolItem);
        $this->assertEquals('tool', $toolItem->item_type);
        $this->assertTrue($toolItem->isTool());
        $this->assertEquals(2.0, (float)$toolItem->qty_requested);
        $this->assertEquals('unit', $toolItem->custom_item_unit);

        $matItem = $mr->items()->where('custom_item_name', 'Triplek 12mm Custom')->first();
        $this->assertNotNull($matItem);
        $this->assertEquals('material', $matItem->item_type);
        $this->assertFalse($matItem->isTool());
        $this->assertEquals(15.5, (float)$matItem->qty_requested);

        // Test show view renders the tag
        $showResponse = $this->actingAs($this->karyawan)->get(route('material-requests.show', $mr));
        $showResponse->assertStatus(200);
        $showResponse->assertSee('Input Manual (Alat)');
        $showResponse->assertSee('Input Manual (Material)');
    }

    public function test_cannot_submit_custom_tool_with_decimal_qty(): void
    {
        $toolCategory = Category::where('type', 'tool')->first();

        $payload = [
            'warehouse_id'        => $this->projectWarehouse->id,
            'source_warehouse_id' => null,
            'requested_as'        => 'Admin Proyek',
            'notes'               => 'Test invalid decimal tool qty',
            'action'              => 'submit',
            'custom_items'        => [
                [
                    'name'        => 'Bor Listrik Rotary',
                    'item_type'   => 'tool',
                    'category_id' => $toolCategory?->id,
                    'unit'        => 'unit',
                    'qty'         => 2.5, // Invalid decimal for tool
                    'spec'        => 'Heavy duty',
                ],
            ],
        ];

        $response = $this->actingAs($this->karyawan)->post(route('material-requests.store'), $payload);
        $response->assertSessionHasErrors();

        $this->assertDatabaseMissing('material_requests', [
            'notes' => 'Test invalid decimal tool qty',
        ]);
    }

    public function test_custom_tool_in_po_and_goods_receipt_auto_registers_tool(): void
    {
        $toolCategory = Category::where('type', 'tool')->first();

        // 1. Karyawan submits MR with custom tool
        $mrPayload = [
            'warehouse_id'        => $this->projectWarehouse->id,
            'source_warehouse_id' => null,
            'requested_as'        => 'Admin Proyek',
            'notes'               => 'PO Lapangan untuk mesin stamper custom',
            'action'              => 'submit',
            'custom_items'        => [
                [
                    'name'        => 'Stamper Kuda Mikasa Custom 80kg',
                    'item_type'   => 'tool',
                    'category_id' => $toolCategory?->id,
                    'unit'        => 'unit',
                    'qty'         => 2,
                    'spec'        => 'Mesin Robin EH12 4-Tak',
                ],
            ],
        ];

        $this->actingAs($this->karyawan)->post(route('material-requests.store'), $mrPayload);
        $mr = MaterialRequest::where('notes', 'PO Lapangan untuk mesin stamper custom')->first();
        $this->assertNotNull($mr);
        $mrToolItem = $mr->items()->first();
        $this->assertNotNull($mrToolItem);
        $this->assertTrue($mrToolItem->isTool());
        $this->assertNull($mrToolItem->tool_id);

        // 2. Admin creates PO from that MR
        $supplier = Supplier::first() ?? Supplier::create([
            'name'  => 'PT Mesin Konstruksi Mandiri',
            'phone' => '081234567890',
        ]);

        $po = PurchaseOrder::create([
            'po_number'             => 'PO-TOOL-AUTO-01',
            'material_request_id'   => $mr->id,
            'supplier_id'           => $supplier->id,
            'supplier_name'         => $supplier->name,
            'delivery_warehouse_id' => $this->projectWarehouse->id,
            'status'                => 'approved',
            'order_date'            => now(),
            'created_by'            => $this->adminPusat->id,
        ]);

        $poItem = PurchaseOrderItem::create([
            'purchase_order_id'        => $po->id,
            'material_request_item_id' => $mrToolItem->id,
            'custom_item_name'         => $mrToolItem->custom_item_name,
            'custom_item_unit'         => 'unit',
            'quantity'                 => 2,
            'unit_price'               => 7500000,
            'subtotal'                 => 15000000,
        ]);

        $this->assertTrue($poItem->isTool());

        // 3. Receive via GoodsReceiptService
        $service = app(GoodsReceiptService::class);
        $gr = $service->create([
            'supplier_id'         => $supplier->id,
            'warehouse_id'        => $this->projectWarehouse->id,
            'purchase_order_id'   => $po->id,
            'material_request_id' => $mr->id,
            'received_at'         => now()->toDateString(),
            'items'               => [
                [
                    'purchase_order_item_id' => $poItem->id,
                    'qty_shipped'            => 2,
                    'qty_received'           => 2,
                    'qty_damaged'            => 0,
                    'condition'              => 'good',
                ],
            ],
        ], $this->karyawan->id);

        $confirmedGr = $service->confirm($gr, $this->karyawan->id);
        $this->assertEquals('confirmed', $confirmedGr->status);

        // 4. Verify tool was auto-registered
        $registeredTool = Tool::where('name', 'Stamper Kuda Mikasa Custom 80kg')->first();
        $this->assertNotNull($registeredTool);
        $this->assertStringStartsWith('TL-AUTO-', $registeredTool->code);
        $this->assertEquals($toolCategory?->id, $registeredTool->category_id);

        // 5. Verify relations are linked
        $poItem->refresh();
        $mrToolItem->refresh();
        $this->assertEquals($registeredTool->id, $poItem->tool_id);
        $this->assertEquals($registeredTool->id, $mrToolItem->tool_id);

        // 6. Verify inventory added to project warehouse
        $toolInv = ToolInventory::where('tool_id', $registeredTool->id)
            ->where('warehouse_id', $this->projectWarehouse->id)
            ->first();
        $this->assertNotNull($toolInv);
        $this->assertEquals(2, $toolInv->stock_total);
        $this->assertEquals(2, $toolInv->stock_available);
    }
}
