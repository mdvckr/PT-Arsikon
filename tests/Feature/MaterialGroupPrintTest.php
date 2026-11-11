<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Inventory;
use App\Models\Material;
use App\Models\StockMutation;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MaterialGroupPrintTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Warehouse $warehouse;
    protected Category $category;
    protected Unit $unit;
    protected Material $material;

    protected function setUp(): void
    {
        parent::setUp();

        Permission::firstOrCreate(['name' => 'view materials', 'guard_name' => 'web']);
        $role = Role::firstOrCreate(['name' => 'Admin Gudang', 'guard_name' => 'web']);
        $role->givePermissionTo('view materials');

        $this->user = User::factory()->create();
        $this->user->assignRole('Admin Gudang');

        $this->warehouse = Warehouse::create([
            'code'       => 'GDG-TEST-01',
            'name'       => 'Gudang Proyek A',
            'is_central' => false,
        ]);
        $this->user->warehouses()->attach($this->warehouse->id);

        $this->category = Category::create([
            'code' => 'CAT-BESI-01',
            'name' => 'Besi & Baja',
            'type' => 'material',
        ]);

        $this->unit = Unit::create([
            'code'       => 'BTM',
            'name'       => 'Batang',
            'is_decimal' => true,
        ]);

        $this->material = Material::create([
            'category_id' => $this->category->id,
            'unit_id'     => $this->unit->id,
            'sku'         => 'MAT-BSI-10',
            'name'        => 'Besi Beton Ulir 10mm',
            'type'        => 'Besi Beton',
            'incoming_stages' => [
                [
                    'stage'        => 'PO-2026-001',
                    'reference'    => 'PO-2026-001 (Tahap 1)',
                    'qty'          => 150.50,
                    'planned_qty'  => 150.50,
                    'received_qty' => 150.50,
                    'date'         => '2026-11-01',
                    'status'       => 'received',
                    'status_label' => 'Lengkap Masuk',
                ]
            ],
        ]);

        Inventory::create([
            'warehouse_id' => $this->warehouse->id,
            'material_id'  => $this->material->id,
            'quantity'     => 125.75,
            'min_stock'    => 10.00,
        ]);

        StockMutation::create([
            'warehouse_id'      => $this->warehouse->id,
            'material_id'       => $this->material->id,
            'reference_type'    => 'usage',
            'qty_change'        => -24.75,
            'qty_balance_after' => 125.75,
        ]);
    }

    public function test_authorized_user_can_view_material_group_print_page(): void
    {
        $response = $this->actingAs($this->user)->get(route('materials.print-group', [
            'category_id'  => $this->category->id,
            'type'         => 'Besi Beton',
            'warehouse_id' => $this->warehouse->id,
        ]));

        $response->assertOk();
        $response->assertViewIs('materials.print-group');
        $response->assertSee('Besi Beton');
        $response->assertSee('MAT-BSI-10');
        $response->assertSee('125,75'); // Sisa stok dengan desimal
        $response->assertSee('24,75');  // Pemakaian dengan desimal
        $response->assertSee('150,50'); // Total/tahap masuk dengan desimal
        $response->assertSee('01/11/2026'); // Tanggal kedatangan tahap
    }

    public function test_unauthorized_user_cannot_access_print_group(): void
    {
        $guest = User::factory()->create();
        $response = $this->actingAs($guest)->get(route('materials.print-group', [
            'category_id' => $this->category->id,
            'type'        => 'Besi Beton',
        ]));
        $response->assertStatus(403);
    }
}
