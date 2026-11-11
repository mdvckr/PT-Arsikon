<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Inventory;
use App\Models\Material;
use App\Models\Tool;
use App\Models\ToolInventory;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MaterialMonthlyStockExportTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Warehouse $warehouse;
    protected Category $categoryMat;
    protected Category $categoryTool;
    protected Unit $unit;
    protected Material $material;
    protected Tool $tool;

    protected function setUp(): void
    {
        parent::setUp();

        $permissions = [
            'view materials',
            'view tools',
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        $role = Role::firstOrCreate(['name' => 'Admin Pusat', 'guard_name' => 'web']);
        $role->syncPermissions($permissions);

        $this->user = User::factory()->create();
        $this->user->assignRole('Admin Pusat');

        $this->warehouse = Warehouse::create([
            'code'       => 'WH-EXP-01',
            'name'       => 'Gudang Pusat Arsikon',
            'is_central' => true,
        ]);
        $this->user->warehouses()->attach($this->warehouse->id);

        $this->categoryMat = Category::create([
            'code' => 'CAT-MAT-01',
            'name' => 'Material Konstruksi',
            'type' => 'material',
        ]);

        $this->categoryTool = Category::create([
            'code' => 'CAT-TOOL-01',
            'name' => 'Peralatan Berat & Ringan',
            'type' => 'tool',
        ]);

        $this->unit = Unit::create([
            'code' => 'SAK',
            'name' => 'Sak',
        ]);

        $this->material = Material::create([
            'category_id' => $this->categoryMat->id,
            'unit_id'     => $this->unit->id,
            'sku'         => 'MAT-SMN-01',
            'name'        => 'Semen Gresik 40kg',
            'type'        => 'Semen',
        ]);

        Inventory::create([
            'warehouse_id' => $this->warehouse->id,
            'material_id'  => $this->material->id,
            'quantity'     => 150,
            'min_stock'    => 20,
        ]);

        $this->tool = Tool::create([
            'category_id' => $this->categoryTool->id,
            'code'        => 'TOOL-GEN-01',
            'name'        => 'Genset Silent 5000W',
            'type'        => 'Mesin & Listrik',
        ]);

        ToolInventory::create([
            'warehouse_id'      => $this->warehouse->id,
            'tool_id'           => $this->tool->id,
            'stock_total'       => 4,
            'stock_available'   => 3,
            'stock_borrowed'    => 0,
            'stock_maintenance' => 0,
            'stock_damaged'     => 1,
        ]);
    }

    public function test_authorized_user_can_export_monthly_stock_excel(): void
    {
        $response = $this->actingAs($this->user)->get(route('materials.export-monthly', [
            'month'        => now()->month,
            'year'         => now()->year,
            'warehouse_id' => $this->warehouse->id,
        ]));

        $response->assertOk();
        $response->assertHeader('content-disposition');
        $this->assertStringContainsString('.xlsx', $response->headers->get('content-disposition'));
        $this->assertStringContainsString('Rekap-Stok-Material-dan-Alat', $response->headers->get('content-disposition'));
    }

    public function test_unauthorized_user_cannot_export_monthly_stock_excel(): void
    {
        $guest = User::factory()->create();

        $response = $this->actingAs($guest)->get(route('materials.export-monthly'));
        $response->assertStatus(403);
    }
}
