<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Material;
use App\Models\Supplier;
use App\Models\Tool;
use App\Models\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterDataTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
    }

    public function test_master_data_units_and_categories_seeded(): void
    {
        $this->assertGreaterThan(0, Unit::count());
        $this->assertGreaterThan(0, Category::count());
        $this->assertGreaterThan(0, Supplier::count());
        $this->assertEquals(0, Material::count());
        $this->assertEquals(0, Tool::count());
    }

    public function test_material_belongs_to_category_and_unit(): void
    {
        $category = Category::where('type', 'material')->firstOrFail();
        $unit = Unit::firstOrFail();

        $material = Material::create([
            'sku' => 'TEST-MAT-001',
            'name' => 'Semen Padang 50kg',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'is_active' => true,
        ]);

        $this->assertNotNull($material);
        $this->assertNotNull($material->category);
        $this->assertNotNull($material->unit);
    }

    public function test_tool_belongs_to_category(): void
    {
        $category = Category::where('type', 'tool')->firstOrFail();

        $tool = Tool::create([
            'code' => 'TEST-TOOL-001',
            'name' => 'Bor Tangan Listrik',
            'category_id' => $category->id,
            'is_active' => true,
        ]);

        $this->assertNotNull($tool);
        $this->assertNotNull($tool->category);
    }

    public function test_can_create_tool_with_initial_stock(): void
    {
        $admin = \App\Models\User::where('email', 'admin.pusat@arsikon.co.id')->firstOrFail();
        $warehouse = \App\Models\Warehouse::where('is_central', true)->firstOrFail();
        $category = Category::where('type', 'tool')->firstOrFail();

        $response = $this->actingAs($admin)->post(route('tools.store'), [
            'code'         => 'TOOL-TEST-001',
            'name'         => 'Bor Listrik Bosch 13mm',
            'type'         => 'Bor Listrik',
            'brand'        => 'Bosch',
            'category_id'  => $category->id,
            'warehouse_id' => $warehouse->id,
            'stock_total'  => 15,
        ]);

        $response->assertRedirect(route('tools.index'));

        $tool = Tool::where('code', 'TOOL-TEST-001')->first();
        $this->assertNotNull($tool);
        $this->assertEquals(15, $tool->stock_total);
        $this->assertEquals(15, $tool->stock_available);

        $inv = \App\Models\ToolInventory::where('warehouse_id', $warehouse->id)
            ->where('tool_id', $tool->id)
            ->first();
        $this->assertNotNull($inv);
        $this->assertEquals(15, $inv->stock_total);
        $this->assertEquals(15, $inv->stock_available);
    }

    public function test_can_add_stock_to_existing_tool(): void
    {
        $admin = \App\Models\User::where('email', 'admin.pusat@arsikon.co.id')->firstOrFail();
        $category = Category::where('type', 'tool')->firstOrFail();
        $tool = Tool::create([
            'code' => 'TOOL-EXISTING-01',
            'name' => 'Genset Silent 5000W',
            'category_id' => $category->id,
            'stock_total' => 5,
            'stock_available' => 5,
            'is_active' => true,
        ]);
        $initialStock = $tool->stock_total;

        $response = $this->actingAs($admin)->post(route('tools.addStock', $tool), [
            'quantity' => 10,
        ]);

        $response->assertRedirect(route('tools.edit', $tool));

        $tool->refresh();
        $this->assertEquals($initialStock + 10, $tool->stock_total);
    }

    public function test_can_update_material_with_supplier_and_new_category_and_unit(): void
    {
        $admin = \App\Models\User::where('email', 'admin.pusat@arsikon.co.id')->firstOrFail();
        $category = Category::where('type', 'material')->firstOrFail();
        $unit = Unit::firstOrFail();

        $material = Material::create([
            'sku' => 'MAT-UPDATE-TEST',
            'name' => 'Semen Gresik Awal',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->put(route('materials.update', $material), [
            'sku'          => $material->sku,
            'name'         => 'Semen Gresik Super 50kg',
            'brand'        => 'Gresik',
            'size'         => '50kg',
            'type'         => 'Semen',
            'supplier'     => 'PT Semen Indonesia Sejahtera',
            'new_category' => 'Bahan Perekat & Adukan',
            'new_unit'     => 'Zak',
            'description'  => 'Material update test description',
        ]);

        $response->assertRedirect(route('materials.index'));
        $response->assertSessionHas('success');

        $material->refresh();
        $this->assertEquals('Semen Gresik Super 50kg', $material->name);
        $this->assertEquals('Gresik', $material->brand);
        $this->assertEquals('PT Semen Indonesia Sejahtera', $material->supplier_name);
        $this->assertEquals('Bahan Perekat & Adukan', $material->category->name);
        $this->assertEquals('Zak', $material->unit->name);
    }
}
