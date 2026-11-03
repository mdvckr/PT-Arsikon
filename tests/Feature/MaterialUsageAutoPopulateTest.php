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

class MaterialUsageAutoPopulateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
    }

    public function test_store_filters_out_items_with_zero_or_empty_quantity(): void
    {
        $warehouse = Warehouse::where('is_central', false)->firstOrFail();
        $admin = User::where('email', 'admin.proyek1@arsikon.co.id')->firstOrFail();
        $category = Category::firstOrCreate(['code' => 'CAT-TEST'], ['name' => 'Umum', 'type' => 'material']);
        $unit = Unit::firstOrCreate(['code' => 'SAK'], ['name' => 'Sak', 'abbreviation' => 'sak']);

        $mat1 = Material::create([
            'sku'         => 'MAT-AUTO-001',
            'name'        => 'Semen Gresik 40kg',
            'category_id' => $category->id,
            'unit_id'     => $unit->id,
            'is_active'   => true,
        ]);
        $mat2 = Material::create([
            'sku'         => 'MAT-AUTO-002',
            'name'        => 'Pasir Pasang',
            'category_id' => $category->id,
            'unit_id'     => $unit->id,
            'is_active'   => true,
        ]);

        Inventory::create(['warehouse_id' => $warehouse->id, 'material_id' => $mat1->id, 'quantity' => 50]);
        Inventory::create(['warehouse_id' => $warehouse->id, 'material_id' => $mat2->id, 'quantity' => 20]);

        $response = $this->actingAs($admin)->post(route('material-usages.store'), [
            'warehouse_id'   => $warehouse->id,
            'recipient_name' => 'Mandor Sugeng',
            'usage_date'     => now()->toDateString(),
            'items'          => [
                ['material_id' => $mat1->id, 'quantity' => '5', 'notes' => 'Pondasi'],
                ['material_id' => $mat2->id, 'quantity' => '', 'notes' => ''], // Unfilled auto-populated row
            ],
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();
        $this->assertDatabaseHas('material_usages', ['recipient_name' => 'Mandor Sugeng']);
        $this->assertDatabaseHas('material_usage_items', ['material_id' => $mat1->id, 'quantity' => 5]);
        $this->assertDatabaseMissing('material_usage_items', ['material_id' => $mat2->id]);
    }

    public function test_create_page_contains_warehouse_stock_materials(): void
    {
        $warehouse = Warehouse::where('is_central', false)->firstOrFail();
        $admin = User::where('email', 'admin.proyek1@arsikon.co.id')->firstOrFail();
        $category = Category::firstOrCreate(['code' => 'CAT-TEST'], ['name' => 'Umum', 'type' => 'material']);
        $unit = Unit::firstOrCreate(['code' => 'SAK'], ['name' => 'Sak', 'abbreviation' => 'sak']);

        $mat = Material::create([
            'sku'         => 'MAT-AUTO-003',
            'name'        => 'Batu Belah Kali 15cm',
            'category_id' => $category->id,
            'unit_id'     => $unit->id,
            'is_active'   => true,
        ]);

        Inventory::create([
            'warehouse_id' => $warehouse->id,
            'material_id'  => $mat->id,
            'quantity'     => 75,
        ]);

        $response = $this->actingAs($admin)->get(route('material-usages.create', ['warehouse_id' => $warehouse->id]));

        $response->assertStatus(200);
        $response->assertSee('Batu Belah Kali 15cm');
        $response->assertSee('availableMaterials');
        $response->assertSee('initMaterialRows');
    }
}

