<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\GoodsReceipt;
use App\Models\Material;
use App\Models\Supplier;
use App\Models\Tool;
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
        $response->assertSee('Penerimaan Barang');
        $response->assertSee('Tambah Baris Barang');
        $response->assertSee('Gudang Tujuan');
        $response->assertSee('name="warehouse_id"', false);
        $response->assertDontSee('Pilih Purchase Order (Wajib)');
        $response->assertDontSee('Hubungkan PO Lapangan');
        $response->assertDontSee('Hubungkan Purchase Order (PO)');
        $response->assertDontSee('name="purchase_order_id"', false);
        $response->assertDontSee('name="material_request_id"', false);
        $response->assertDontSee('Qty Target (PO)');
        $response->assertSee('Qty Nota / SJ');
        $response->assertSee('Selisih &amp; Status', false);
        $response->assertSee('Diterima (Stok)');
        $response->assertSee('Kode Material');
    }

    public function test_goods_receipt_create_only_references_items_from_users_own_warehouse(): void
    {
        $unit = \App\Models\Unit::firstOrCreate(['code' => 'ZAK'], ['name' => 'Zak']);
        $otherWarehouse = Warehouse::firstOrCreate(['code' => 'W-OTHER-002'], ['name' => 'Gudang Proyek Lain', 'is_central' => false]);

        $materialOwn = \App\Models\Material::create([
            'sku' => 'MAT-OWN-01',
            'name' => 'Semen Padang Gudang Sendiri',
            'category_id' => $this->category->id,
            'unit_id' => $unit->id,
        ]);
        \App\Models\Inventory::create([
            'material_id' => $materialOwn->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 10,
        ]);

        $materialOther = \App\Models\Material::create([
            'sku' => 'MAT-OTHER-02',
            'name' => 'Besi Ulir Gudang Orang Lain',
            'category_id' => $this->category->id,
            'unit_id' => $unit->id,
        ]);
        \App\Models\Inventory::create([
            'material_id' => $materialOther->id,
            'warehouse_id' => $otherWarehouse->id,
            'quantity' => 20,
        ]);

        $response = $this->actingAs($this->gudangUser)->get(route('goods-receipts.create'));
        $response->assertOk();

        $responseMaterials = $response->viewData('materials');
        $this->assertTrue($responseMaterials->contains('id', $materialOwn->id));
        $this->assertFalse($responseMaterials->contains('id', $materialOther->id));
    }

    public function test_goods_receipt_edit_view_renders_direct_item_inputs_without_po_select(): void
    {
        $gr = GoodsReceipt::create([
            'receipt_number' => 'GR-TEST-001',
            'warehouse_id' => $this->warehouse->id,
            'supplier_id' => $this->supplier->id,
            'receipt_date' => now()->toDateString(),
            'status' => 'draft',
            'received_by_user_id' => $this->gudangUser->id,
        ]);

        $response = $this->actingAs($this->gudangUser)->get(route('goods-receipts.edit', $gr));
        $response->assertOk();
        $response->assertSee('Tambah Baris Barang');
        $response->assertDontSee('Hubungkan PO Lapangan');
        $response->assertDontSee('Hubungkan Purchase Order (PO)');
        $response->assertDontSee('name="purchase_order_id"', false);
        $response->assertDontSee('name="material_request_id"', false);
        $response->assertDontSee('Qty Target (PO)');
        $response->assertSee('Qty Nota / SJ');
        $response->assertSee('Selisih &amp; Status', false);
        $response->assertSee('Diterima (Stok)');
        $response->assertSee('Kategori');
        $response->assertSee('Satuan');
        $response->assertSee('Kode Material');
    }

    public function test_can_update_draft_goods_receipt_with_direct_material_inputs(): void
    {
        $gr = GoodsReceipt::create([
            'receipt_number' => 'GR-TEST-UPDATE-01',
            'warehouse_id' => $this->warehouse->id,
            'supplier_id' => $this->supplier->id,
            'receipt_date' => now()->toDateString(),
            'status' => 'draft',
            'received_by_user_id' => $this->gudangUser->id,
        ]);

        $response = $this->actingAs($this->gudangUser)->put(route('goods-receipts.update', $gr), [
            'warehouse_id'    => $this->warehouse->id,
            'supplier_id'     => $this->supplier->id,
            'received_at'     => now()->toDateString(),
            'invoice_number'  => 'INV-UPDATED-99',
            'notes'           => 'Update draft catatan',
            'items' => [
                [
                    'item_type'     => 'material',
                    'name'          => 'Bata Merah Press Super',
                    'category_id'   => $this->category->id,
                    'custom_unit'   => 'pcs',
                    'quantity'      => 1000,
                    'condition'     => 'good',
                ]
            ]
        ]);

        $response->assertSessionHasNoErrors();
        $gr->refresh();
        $this->assertEquals('INV-UPDATED-99', $gr->invoice_number);
        $this->assertCount(1, $gr->items);
        $this->assertEquals('Bata Merah Press Super', $gr->items->first()->item_name);
        $this->assertEquals(1000, (float) $gr->items->first()->qty_received);
    }

    public function test_direct_goods_receipt_with_discrepancy_saves_missing_qty_and_records_in_dynamic_stages(): void
    {
        $response = $this->actingAs($this->gudangUser)->post(route('goods-receipts.store'), [
            'warehouse_id'    => $this->warehouse->id,
            'supplier_id'     => $this->supplier->id,
            'received_at'     => now()->toDateString(),
            'invoice_number'  => 'DO-VENDOR-SELISIH-01',
            'notes'           => 'Barang ada selisih di jalan',
            'items' => [
                [
                    'item_type'     => 'material',
                    'name'          => 'Semen Gresik 50kg Selisih',
                    'category_id'   => $this->category->id,
                    'custom_unit'   => 'sak',
                    'qty_shipped'   => 100,
                    'quantity'      => 95,
                    'qty_missing'   => 5,
                    'missing_action'=> 'lost',
                    'condition'     => 'good',
                ]
            ]
        ]);

        $response->assertSessionHasNoErrors();
        $gr = GoodsReceipt::latest('id')->first();
        $this->assertNotNull($gr);
        $item = $gr->items->first();
        $this->assertEquals(100, (float) $item->qty_shipped);
        $this->assertEquals(95, (float) $item->qty_received);
        $this->assertEquals(5, (float) $item->qty_missing);

        // Konfirmasi Goods Receipt
        $service = app(\App\Services\GoodsReceiptService::class);
        $service->confirm($gr, $this->gudangUser->id);

        $item->refresh();
        $this->assertEquals('confirmed_lost', $item->missing_status);

        // Verifikasi material terdaftar dan tahapan masuk mencatat selisih hilang
        $material = \App\Models\Material::where('name', 'Semen Gresik 50kg Selisih')->first();
        $this->assertNotNull($material);

        $stages = $material->getDynamicIncomingStages();
        $this->assertNotEmpty($stages);
        $stage = $stages->first();
        $this->assertEquals(100, $stage['planned_qty']);
        $this->assertEquals(95, $stage['received_qty']);
        $this->assertEquals(5, $stage['lost_qty']);
        $this->assertEquals('partial', $stage['status']);
        $this->assertStringContainsString('Masuk Ada Selisih', $stage['status_label']);
    }

    public function test_direct_goods_receipt_with_pending_discrepancy_records_as_belum_dikirim_semua(): void
    {
        $response = $this->actingAs($this->gudangUser)->post(route('goods-receipts.store'), [
            'warehouse_id'    => $this->warehouse->id,
            'supplier_id'     => $this->supplier->id,
            'received_at'     => now()->toDateString(),
            'invoice_number'  => 'DO-VENDOR-PENDING-02',
            'notes'           => 'Armada pertama baru bawa 70, sisanya besok',
            'items' => [
                [
                    'item_type'     => 'material',
                    'name'          => 'Besi Beton Ulir 12mm',
                    'category_id'   => $this->category->id,
                    'custom_unit'   => 'batang',
                    'qty_shipped'   => 100,
                    'quantity'      => 70,
                    'qty_missing'   => 30,
                    'missing_action'=> 'pending',
                    'condition'     => 'good',
                ]
            ]
        ]);

        $response->assertSessionHasNoErrors();
        $gr = GoodsReceipt::latest('id')->first();
        $this->assertNotNull($gr);
        $item = $gr->items->first();
        $this->assertEquals(100, (float) $item->qty_shipped);
        $this->assertEquals(70, (float) $item->qty_received);
        $this->assertEquals(30, (float) $item->qty_missing);
        $this->assertEquals('pending', $item->missing_action);

        // Konfirmasi Goods Receipt
        $service = app(\App\Services\GoodsReceiptService::class);
        $service->confirm($gr, $this->gudangUser->id);

        $item->refresh();
        $this->assertEquals('unresolved', $item->missing_status);

        // Verifikasi material terdaftar dan tahapan masuk mencatat 'Belum Dikirim Semua'
        $material = \App\Models\Material::where('name', 'Besi Beton Ulir 12mm')->first();
        $this->assertNotNull($material);

        $stages = $material->getDynamicIncomingStages();
        $this->assertNotEmpty($stages);
        $stage = $stages->first();
        $this->assertEquals(100, $stage['planned_qty']);
        $this->assertEquals(70, $stage['received_qty']);
        $this->assertEquals(30, $stage['lost_qty']);
        $this->assertEquals('partial', $stage['status']);
        $this->assertStringContainsString('Belum Dikirim Semua', $stage['status_label']);
        $this->assertStringContainsString('Sisa 30', $stage['status_label']);
    }

    public function test_same_item_name_with_different_size_or_subgroup_creates_separate_inventory_items(): void
    {
        // 1. Simpan item pertama: ARSITEK -> BATA -> Bata Merah (ukuran 10m)
        $response1 = $this->actingAs($this->gudangUser)->post(route('goods-receipts.store'), [
            'warehouse_id'    => $this->warehouse->id,
            'supplier_id'     => $this->supplier->id,
            'received_at'     => now()->toDateString(),
            'invoice_number'  => 'DO-BATA-10M',
            'items' => [
                [
                    'item_type'     => 'material',
                    'name'          => 'BATA MERAH',
                    'category_id'   => $this->category->id,
                    'type'          => 'BATA',
                    'brand'         => 'Custom / Proyek',
                    'size'          => '10m',
                    'custom_unit'   => 'pcs',
                    'qty_received'  => 1000,
                    'condition'     => 'good',
                ]
            ]
        ]);
        $response1->assertSessionHasNoErrors();

        // 2. Simpan item kedua dengan nama SAMA tetapi ukuran BEDA (ukuran 20m)
        $response2 = $this->actingAs($this->gudangUser)->post(route('goods-receipts.store'), [
            'warehouse_id'    => $this->warehouse->id,
            'supplier_id'     => $this->supplier->id,
            'received_at'     => now()->toDateString(),
            'invoice_number'  => 'DO-BATA-20M',
            'items' => [
                [
                    'item_type'     => 'material',
                    'name'          => 'BATA MERAH',
                    'category_id'   => $this->category->id,
                    'type'          => 'BATA',
                    'brand'         => 'Custom / Proyek',
                    'size'          => '20m',
                    'custom_unit'   => 'pcs',
                    'qty_received'  => 500,
                    'condition'     => 'good',
                ]
            ]
        ]);
        $response2->assertSessionHasNoErrors();

        // Verifikasi ada 2 material terpisah di database
        $materials = \App\Models\Material::where('name', 'BATA MERAH')->get();
        $this->assertCount(2, $materials);

        $mat10 = $materials->where('size', '10m')->first();
        $mat20 = $materials->where('size', '20m')->first();

        $this->assertNotNull($mat10);
        $this->assertNotNull($mat20);
        $this->assertNotEquals($mat10->id, $mat20->id);
        $this->assertNotEquals($mat10->sku, $mat20->sku);
        $this->assertEquals('BATA', $mat10->type);
        $this->assertEquals('BATA', $mat20->type);
    }

    public function test_create_and_edit_views_provide_real_database_groups_and_units(): void
    {
        $this->actingAs($this->gudangUser);

        $response = $this->get(route('goods-receipts.create'));
        $response->assertStatus(200);
        $response->assertViewHas('units');
        $response->assertViewHas('existingMaterialGroups');
        $response->assertViewHas('existingToolGroups');
        $response->assertViewHas('allMaterialGroups');
        $response->assertViewHas('allToolGroups');
        $response->assertSee('window.grUnits', false);
        $response->assertSee('window.grMaterialGroups', false);
        $response->assertSee('window.grToolGroups', false);
    }

    public function test_custom_item_with_database_unit_registers_correctly_to_inventory(): void
    {
        $this->actingAs($this->gudangUser);
        $warehouse = $this->warehouse;
        $category = Category::firstOrCreate(['name' => 'ARSITEK', 'type' => 'material'], ['code' => 'CAT-TEST-ARS']);
        $unit = \App\Models\Unit::firstOrCreate(['code' => 'pcs'], ['name' => 'Pieces / Buah', 'is_decimal' => false]);

        $postData = [
            'received_at'    => now()->toDateString(),
            'warehouse_id'   => $warehouse->id,
            'supplier_id'    => $this->supplier->id,
            'invoice_number' => 'DO-REAL-DB-001',
            'notes'          => 'Test unit riil DB',
            'items'          => [
                [
                    'item_type'     => 'material',
                    'category_id'   => $category->id,
                    'type'          => 'Eksterior',
                    'name'          => 'Bata Merah Uji DB',
                    'brand'         => 'Pabrik Uji',
                    'custom_unit'   => $unit->code,
                    'quantity'      => 500,
                    'qty_received'  => 500,
                    'condition'     => 'good',
                ]
            ]
        ];

        $response = $this->post(route('goods-receipts.store'), $postData);
        $response->assertSessionHasNoErrors();

        // Verify material was registered with exact DB unit and type
        $this->assertDatabaseHas('materials', [
            'name'        => 'Bata Merah Uji DB',
            'category_id' => $category->id,
            'type'        => 'Eksterior',
            'unit_id'     => $unit->id,
        ]);

        $gr = GoodsReceipt::latest('id')->first();
        $this->assertNotNull($gr);
        $this->post(route('goods-receipts.confirm', $gr));

        // Verify inventory was created in the warehouse
        $this->assertDatabaseHas('inventories', [
            'warehouse_id' => $warehouse->id,
            'quantity'     => 500,
        ]);
    }

    public function test_custom_item_with_newly_typed_category_and_subgroup_registers_and_creates_inventory(): void
    {
        $this->actingAs($this->gudangUser);

        $warehouse = $this->warehouse;
        $supplier = $this->supplier;

        $postData = [
            'supplier_id'      => $supplier->id,
            'warehouse_id'     => $warehouse->id,
            'received_at'      => now()->format('Y-m-d'),
            'received_by_name' => 'Petugas Lapangan',
            'items'            => [
                [
                    'item_type'     => 'material',
                    'category_name' => 'Struktur Atas Khusus',
                    'type'          => 'Bata Khusus',
                    'name'          => 'Bata Merah Pres Super M3',
                    'brand'         => 'Merah Pres Super',
                    'custom_unit'   => 'M3',
                    'quantity'      => 150,
                    'qty_received'  => 150,
                    'condition'     => 'good',
                ]
            ]
        ];

        $response = $this->post(route('goods-receipts.store'), $postData);
        $response->assertSessionHasNoErrors();

        // Verify category was automatically created in database
        $this->assertDatabaseHas('categories', [
            'name' => 'Struktur Atas Khusus',
            'type' => 'material',
        ]);
        $createdCat = Category::where('name', 'Struktur Atas Khusus')->first();

        // Verify material was registered with that category and custom type
        $this->assertDatabaseHas('materials', [
            'name'        => 'Bata Merah Pres Super M3',
            'category_id' => $createdCat->id,
            'type'        => 'Bata Khusus',
            'brand'       => 'Merah Pres Super',
        ]);

        $gr = GoodsReceipt::latest('id')->first();
        $this->assertNotNull($gr);
        $this->post(route('goods-receipts.confirm', $gr));

        // Verify inventory was created in the warehouse
        $this->assertDatabaseHas('inventories', [
            'warehouse_id' => $warehouse->id,
            'quantity'     => 150,
        ]);
    }

    public function test_goods_receipt_create_view_shows_po_select_for_project_manager_and_site_manager(): void
    {
        $pmUser = User::factory()->create(['email_verified_at' => now()]);
        $pmUser->assignRole('Project Manager');
        $pmUser->warehouses()->attach($this->warehouse->id);

        $response = $this->actingAs($pmUser)->get(route('goods-receipts.create'));
        $response->assertOk();
        $response->assertSee('Hubungkan PO Lapangan');
        $response->assertSee('Hubungkan Purchase Order (PO)');
        $response->assertSee('name="purchase_order_id"', false);
        $response->assertSee('name="material_request_id"', false);
    }

    public function test_admin_gudang_proyek_stores_goods_receipt_with_auto_selected_warehouse(): void
    {
        // Admin gudang proyek with assigned warehouse
        $postData = [
            'supplier_id'      => $this->supplier->id,
            'received_at'      => now()->format('Y-m-d'),
            'received_by_name' => 'Logistik Lapangan',
            'items'            => [
                [
                    'item_type'     => 'material',
                    'name'          => 'Batu Split 2/3 Logistik',
                    'category_id'   => $this->category->id,
                    'type'          => 'Batu',
                    'custom_unit'   => 'm3',
                    'quantity'      => 20,
                    'qty_received'  => 20,
                    'condition'     => 'good',
                ]
            ]
        ];

        // Notice warehouse_id is not passed explicitly in payload; backend falls back to assigned single warehouse
        $response = $this->actingAs($this->gudangUser)->post(route('goods-receipts.store'), $postData);
        $response->assertSessionHasNoErrors();

        $gr = GoodsReceipt::latest('id')->first();
        $this->assertNotNull($gr);
        $this->assertEquals($this->warehouse->id, $gr->warehouse_id);
    }

    public function test_custom_item_with_size_or_specification_persists_size_and_renders_column(): void
    {
        // 1. Verify create view renders the header column
        $createRes = $this->actingAs($this->gudangUser)->get(route('goods-receipts.create'));
        $createRes->assertStatus(200);
        $createRes->assertSee('Ukuran / Spesifikasi');

        // 2. Submit goods receipt with custom item having size
        $postData = [
            'warehouse_id'     => $this->warehouse->id,
            'supplier_id'      => $this->supplier->id,
            'received_at'      => now()->format('Y-m-d'),
            'received_by_name' => 'Logistik Lapangan',
            'items'            => [
                [
                    'item_type'     => 'material',
                    'name'          => 'Besi Beton Polos 10mm Sni Kg',
                    'size'          => '10mm',
                    'brand'         => 'Sni',
                    'type'          => 'Besi',
                    'custom_unit'   => 'kg',
                    'quantity'      => 100,
                    'qty_shipped'   => 100,
                    'condition'     => 'good',
                ],
                [
                    'item_type'     => 'tool',
                    'name'          => 'Genset Silent 5000W Honda Unit',
                    'size'          => '5000W',
                    'brand'         => 'Honda',
                    'type'          => 'Genset',
                    'custom_unit'   => 'unit',
                    'quantity'      => 1,
                    'qty_shipped'   => 1,
                    'condition'     => 'good',
                ],
            ],
        ];

        $response = $this->actingAs($this->gudangUser)->post(route('goods-receipts.store'), $postData);
        $response->assertSessionHasNoErrors();

        // Check registered Material has size
        $material = Material::where('name', 'Besi Beton Polos 10mm Sni Kg')->first();
        $this->assertNotNull($material);
        $this->assertEquals('10mm', $material->size);

        // Check registered Tool has size
        $tool = \App\Models\Tool::where('name', 'Genset Silent 5000W Honda Unit')->first();
        $this->assertNotNull($tool);
        $this->assertEquals('5000W', $tool->size);

        // Check edit view renders the column
        $gr = GoodsReceipt::latest('id')->first();
        $editRes = $this->actingAs($this->gudangUser)->get(route('goods-receipts.edit', $gr));
        $editRes->assertStatus(200);
        $editRes->assertSee('Ukuran / Spesifikasi');
    }

    public function test_goods_receipt_brand_is_decoupled_from_item_name(): void
    {
        // 1. Check frontend script has decoupled brand input from syncAutoItemName
        $js = file_get_contents(public_path('js/goods-receipt-create.js'));
        $this->assertStringNotContainsString("id=\"item-brand-' + idx + '\" class=\"form-control\" oninput=\"syncAutoItemName", $js);
        $this->assertStringNotContainsString("id=\"item-brand-' + idx + '\" class=\"form-control\" placeholder=\"Merek...\" value=\"' + escHtml(initBrand) + '\" oninput=\"syncAutoItemName", $js);

        // 2. Submit goods receipt with custom item having distinct name and brand
        $postData = [
            'warehouse_id'     => $this->warehouse->id,
            'received_at'      => now()->format('Y-m-d'),
            'received_by_name' => 'Petugas Gudang',
            'supplier_id'      => $this->supplier->id,
            'items'            => [
                [
                    'item_type'     => 'material',
                    'name'          => 'Cat Dinding Interior Premium',
                    'size'          => '5kg',
                    'brand'         => 'Dulux Weathershield',
                    'type'          => 'Cat',
                    'custom_unit'   => 'pail',
                    'quantity'      => 10,
                    'qty_shipped'   => 10,
                    'condition'     => 'good',
                ],
            ],
        ];

        $response = $this->actingAs($this->gudangUser)->post(route('goods-receipts.store'), $postData);
        $response->assertSessionHasNoErrors();

        // 3. Verify material registered has name without brand contamination
        $material = Material::where('name', 'Cat Dinding Interior Premium')->first();
        $this->assertNotNull($material, 'Material should be saved with exact name');
        $this->assertEquals('Dulux Weathershield', $material->brand);
        $this->assertEquals('5kg', $material->size);

        // 4. Verify goods receipt item references the material
        $gr = GoodsReceipt::latest('id')->first();
        $this->assertNotNull($gr);
        $this->assertDatabaseHas('goods_receipt_items', [
            'goods_receipt_id' => $gr->id,
            'material_id'      => $material->id,
        ]);
    }
}

