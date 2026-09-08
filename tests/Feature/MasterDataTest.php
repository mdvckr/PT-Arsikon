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
        $this->assertGreaterThan(0, Material::count());
        $this->assertGreaterThan(0, Tool::count());
    }

    public function test_material_belongs_to_category_and_unit(): void
    {
        $material = Material::first();

        $this->assertNotNull($material);
        $this->assertNotNull($material->category);
        $this->assertNotNull($material->unit);
    }

    public function test_tool_belongs_to_category(): void
    {
        $tool = Tool::first();

        $this->assertNotNull($tool);
        $this->assertNotNull($tool->category);
    }
}
