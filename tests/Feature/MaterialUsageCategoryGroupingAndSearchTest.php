<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Inventory;
use App\Models\Material;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaterialUsageCategoryGroupingAndSearchTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Warehouse $warehouse;
    protected Category $catStruktur;
    protected Category $catArsitek;
    protected Category $catMep;
    protected Unit $sak;
    protected Unit $btg;
    protected Unit $pcs;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $this->warehouse = Warehouse::where('is_central', false)->firstOrFail();
        $this->user = User::where('email', 'admin.proyek1@arsikon.co.id')->firstOrFail();

        $this->catStruktur = Category::firstOrCreate(['name' => 'STRUKTUR'], ['code' => 'CAT-STR', 'type' => 'material']);
        $this->catArsitek  = Category::firstOrCreate(['name' => 'ARSITEK'], ['code' => 'CAT-ARS', 'type' => 'material']);
        $this->catMep      = Category::firstOrCreate(['name' => 'MEP'], ['code' => 'CAT-MEP', 'type' => 'material']);

        $this->sak = Unit::firstOrCreate(['code' => 'SAK'], ['name' => 'Sak', 'abbreviation' => 'sak']);
        $this->btg = Unit::firstOrCreate(['code' => 'BTG'], ['name' => 'Batang', 'abbreviation' => 'btg']);
        $this->pcs = Unit::firstOrCreate(['code' => 'PCS'], ['name' => 'Pcs', 'abbreviation' => 'pcs']);
    }

    public function test_create_view_provides_grouped_materials_with_category_metadata(): void
    {
        $mat1 = Material::create(['sku' => 'MAT-STR-01', 'name' => 'Semen Portland 50kg', 'category_id' => $this->catStruktur->id, 'unit_id' => $this->sak->id, 'is_active' => true]);
        $mat2 = Material::create(['sku' => 'MAT-ARS-01', 'name' => 'Cat Dinding Eksterior White 25kg', 'category_id' => $this->catArsitek->id, 'unit_id' => $this->pcs->id, 'is_active' => true]);
        $mat3 = Material::create(['sku' => 'MAT-MEP-01', 'name' => 'Pipa PVC AW 3 inch', 'category_id' => $this->catMep->id, 'unit_id' => $this->btg->id, 'is_active' => true]);

        Inventory::create(['warehouse_id' => $this->warehouse->id, 'material_id' => $mat1->id, 'quantity' => 100]);
        Inventory::create(['warehouse_id' => $this->warehouse->id, 'material_id' => $mat2->id, 'quantity' => 20]);
        Inventory::create(['warehouse_id' => $this->warehouse->id, 'material_id' => $mat3->id, 'quantity' => 50]);

        $response = $this->actingAs($this->user)->get(route('material-usages.create', ['warehouse_id' => $this->warehouse->id]));

        $response->assertOk();
        $response->assertViewHas('categoriesWithStock');
        $response->assertSee('STRUKTUR');
        $response->assertSee('ARSITEK');
        $response->assertSee('MEP');
        $response->assertSee('Semen Portland 50kg');
        $response->assertSee('Cat Dinding Eksterior White 25kg');
        $response->assertSee('Pipa PVC AW 3 inch');
    }

    public function test_create_view_renders_category_filter_tabs_and_grouped_table_headers(): void
    {
        $mat1 = Material::create(['sku' => 'MAT-STR-02', 'name' => 'Batu Split 2/3', 'category_id' => $this->catStruktur->id, 'unit_id' => $this->sak->id, 'is_active' => true]);
        Inventory::create(['warehouse_id' => $this->warehouse->id, 'material_id' => $mat1->id, 'quantity' => 40]);

        $response = $this->actingAs($this->user)->get(route('material-usages.create', ['warehouse_id' => $this->warehouse->id]));

        $response->assertOk();
        $response->assertSee('tab-category-all');
        $response->assertSee('category-divider-row');
        $response->assertSee('data-category="STRUKTUR"', false);
    }

    public function test_create_view_includes_instant_search_and_category_filtering_scripts(): void
    {
        $response = $this->actingAs($this->user)->get(route('material-usages.create', ['warehouse_id' => $this->warehouse->id]));

        $response->assertOk();
        $response->assertSee('function filterByCategory', false);
        $response->assertSee('function clearTableSearch', false);
        $response->assertSee('currentActiveCategory', false);
        $response->assertSee('startsWith', false);
    }

    public function test_can_submit_material_usage_with_grouped_items_and_deduct_stock(): void
    {
        $matStr = Material::create(['sku' => 'MAT-STR-SUB', 'name' => 'Semen Portland 50kg', 'category_id' => $this->catStruktur->id, 'unit_id' => $this->sak->id, 'is_active' => true]);
        $matArs = Material::create(['sku' => 'MAT-ARS-SUB', 'name' => 'Cat Tembok Putih', 'category_id' => $this->catArsitek->id, 'unit_id' => $this->pcs->id, 'is_active' => true]);

        Inventory::create(['warehouse_id' => $this->warehouse->id, 'material_id' => $matStr->id, 'quantity' => 100]);
        Inventory::create(['warehouse_id' => $this->warehouse->id, 'material_id' => $matArs->id, 'quantity' => 50]);

        $postData = [
            'warehouse_id'   => $this->warehouse->id,
            'recipient_name' => 'Pak Joko Mandor',
            'usage_date'     => now()->toDateString(),
            'job_section'    => 'Pekerjaan Struktur & Finishing',
            'items'          => [
                ['material_id' => $matStr->id, 'quantity' => '10', 'notes' => 'Cor Kolom'],
                ['material_id' => $matArs->id, 'quantity' => '5', 'notes' => 'Cat Dasar'],
            ]
        ];

        $response = $this->actingAs($this->user)->post(route('material-usages.store'), $postData);
        $response->assertSessionHasNoErrors();

        $usage = \App\Models\MaterialUsage::where('recipient_name', 'Pak Joko Mandor')->firstOrFail();
        $response->assertRedirect(route('material-usages.show', $usage));

        $this->assertDatabaseHas('material_usages', ['recipient_name' => 'Pak Joko Mandor']);
        $this->assertDatabaseHas('material_usage_items', ['material_id' => $matStr->id, 'quantity' => 10]);
        $this->assertDatabaseHas('material_usage_items', ['material_id' => $matArs->id, 'quantity' => 5]);

        // Verifikasi stok gudang terpotong dengan benar
        $this->assertDatabaseHas('inventories', ['warehouse_id' => $this->warehouse->id, 'material_id' => $matStr->id, 'quantity' => 90]);
        $this->assertDatabaseHas('inventories', ['warehouse_id' => $this->warehouse->id, 'material_id' => $matArs->id, 'quantity' => 45]);
    }

    public function test_create_view_provides_kelompok_barang_and_material_spec_metadata(): void
    {
        $mat = Material::create([
            'sku'         => 'MAT-STR-SEMEN',
            'name'        => 'Semen Portland Gresik 50kg',
            'category_id' => $this->catStruktur->id,
            'unit_id'     => $this->sak->id,
            'type'        => 'Semen',
            'brand'       => 'Semen Gresik',
            'size'        => '50 kg',
            'is_active'   => true,
        ]);

        Inventory::create(['warehouse_id' => $this->warehouse->id, 'material_id' => $mat->id, 'quantity' => 120]);

        $response = $this->actingAs($this->user)->get(route('material-usages.create', ['warehouse_id' => $this->warehouse->id]));

        $response->assertOk();
        $materials = $response->viewData('materialsData');
        $this->assertNotEmpty($materials);
        $found = collect($materials)->firstWhere('id', $mat->id);
        $this->assertNotNull($found);
        $this->assertEquals('Semen', $found['type']);
        $this->assertEquals('Semen Gresik', $found['brand']);
        $this->assertEquals('50 kg', $found['size']);
        $this->assertEquals('MAT-STR-SEMEN', $found['sku']);
    }

    public function test_table_renders_clean_material_rows_without_per_item_search_inputs(): void
    {
        $mat = Material::create([
            'sku'         => 'MAT-STR-SPLIT',
            'name'        => 'Batu Split 2/3',
            'category_id' => $this->catStruktur->id,
            'unit_id'     => $this->sak->id,
            'type'        => 'Agregat Kasar',
            'is_active'   => true,
        ]);
        Inventory::create(['warehouse_id' => $this->warehouse->id, 'material_id' => $mat->id, 'quantity' => 50]);

        $response = $this->actingAs($this->user)->get(route('material-usages.create', ['warehouse_id' => $this->warehouse->id]));

        $response->assertOk();
        $response->assertSee('Batu Split 2/3');
        $response->assertSee('MAT-STR-SPLIT');
        $response->assertSee('Agregat Kasar');
        // Pastikan tidak ada lagi input search box di dalam baris stok material
        $response->assertDontSee('placeholder="Ketik nama material..."', false);
    }

    public function test_centralized_search_and_subgroup_divider_toggling_scripts_exist(): void
    {
        $response = $this->actingAs($this->user)->get(route('material-usages.create', ['warehouse_id' => $this->warehouse->id]));
        $response->assertOk();
        $response->assertSee('subgroup-divider-row', false);
        $response->assertSee('openAddMaterialModal', false);
        $response->assertSee('clearRowQty', false);
    }

    public function test_views_display_pemakai_instead_of_penerima(): void
    {
        $response = $this->actingAs($this->user)->get(route('material-usages.create', ['warehouse_id' => $this->warehouse->id]));
        $response->assertOk();
        $response->assertSee('Pemakai (Mandor / Tukang)');
        $response->assertDontSee('Penerima (Mandor / Tukang)');

        $responseIndex = $this->actingAs($this->user)->get(route('material-usages.index'));
        $responseIndex->assertOk();
        $responseIndex->assertSee('Pemakai');
    }

    public function test_material_rows_do_not_render_sku_code_badge(): void
    {
        $mat = Material::create([
            'sku'         => 'KODEMAT-TEST-99',
            'name'        => 'Besi Hollow Galvanis 4x4',
            'category_id' => $this->catStruktur->id,
            'unit_id'     => $this->btg->id,
            'type'        => 'Besi Hollow',
            'is_active'   => true,
        ]);
        Inventory::create(['warehouse_id' => $this->warehouse->id, 'material_id' => $mat->id, 'quantity' => 15]);

        $response = $this->actingAs($this->user)->get(route('material-usages.create', ['warehouse_id' => $this->warehouse->id]));
        $response->assertOk();
        $response->assertSee('Besi Hollow Galvanis 4x4');
        // Pastikan badge kode material tidak lagi dirender di teks tabel tampilan
        $response->assertDontSee('>KODEMAT-TEST-99', false);
        $response->assertDontSee('>KODEMAT-TEST-99</span>', false);
    }

    public function test_material_rows_have_collapsible_dropdown_accordion_structure(): void
    {
        $mat = Material::create([
            'sku'         => 'MAT-PAS-01',
            'name'        => 'Pasir Cor Merapi',
            'category_id' => $this->catStruktur->id,
            'unit_id'     => $this->sak->id,
            'type'        => 'Pasir Cor',
            'is_active'   => true,
        ]);
        Inventory::create(['warehouse_id' => $this->warehouse->id, 'material_id' => $mat->id, 'quantity' => 20]);

        $response = $this->actingAs($this->user)->get(route('material-usages.create', ['warehouse_id' => $this->warehouse->id]));
        $response->assertOk();
        $response->assertSee('group-toggle', false);
        $response->assertSee('subgroup-toggle', false);
        $response->assertSee('subgroup-rows', false);
        $response->assertSee('group-chev', false);
        $response->assertSee('subgroup-chev', false);
        $response->assertSee('Buka Semua');
        $response->assertSee('Tutup Semua');
    }
}
