<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Material;
use App\Models\MaterialRequest;
use App\Models\MaterialRequestItem;
use App\Models\Project;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MaterialRequestViewTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Warehouse $centralWarehouse;
    protected Warehouse $projectWarehouse;
    protected Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'Owner', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Admin Pusat', 'guard_name' => 'web']);

        $this->user = User::factory()->create();
        $this->user->assignRole('Admin Pusat');

        $this->centralWarehouse = Warehouse::create([
            'name'       => 'Gudang Pusat',
            'code'       => 'WH-CENTRAL',
            'is_central' => true,
            'is_active'  => true,
        ]);

        $this->project = Project::create([
            'name' => 'Proyek Test UI',
            'code' => 'PRJ-TEST',
            'status' => 'active',
        ]);

        $this->projectWarehouse = Warehouse::create([
            'name'       => 'Gudang Proyek Test UI',
            'code'       => 'WH-PRJ-01',
            'is_central' => false,
            'is_active'  => true,
            'project_id' => $this->project->id,
        ]);

        $this->user->warehouses()->sync([$this->centralWarehouse->id, $this->projectWarehouse->id]);
    }

    public function test_create_page_renders_successfully(): void
    {
        $response = $this->actingAs($this->user)->get(route('material-requests.create'));
        $response->assertStatus(200);
        $response->assertSee('Buat PO Lapangan');
        $response->assertSee('Daftar Material Tersedia');
        $response->assertSee('Barang Khusus / Input Manual Proyek');
        $response->assertSee('Ajukan PO Lapangan');
    }

    public function test_show_page_renders_successfully(): void
    {
        $category = Category::create(['name' => 'Semen & Pasir', 'code' => 'CAT-SMP', 'type' => 'material']);
        $unit = Unit::create(['name' => 'Sak', 'code' => 'sak']);
        $material = Material::create([
            'name' => 'Semen Gresik 40kg',
            'sku' => 'SKU-SMN-01',
            'unit_id' => $unit->id,
            'category_id' => $category->id,
            'is_active' => true,
        ]);

        $mr = MaterialRequest::create([
            'request_number' => 'REQ-20261013-0001',
            'from_warehouse_id' => null,
            'to_warehouse_id' => $this->projectWarehouse->id,
            'requested_by_user_id' => $this->user->id,
            'status' => 'approved',
            'needed_at' => now()->addDays(2),
            'notes' => 'Catatan tes UI',
        ]);

        MaterialRequestItem::create([
            'material_request_id' => $mr->id,
            'material_id' => $material->id,
            'qty_requested' => 100,
            'qty_approved' => 100,
            'specification' => 'Type 1',
            'usage_location' => 'Zona 1',
            'notes' => 'Catatan item',
        ]);

        $response = $this->actingAs($this->user)->get(route('material-requests.show', $mr));
        $response->assertStatus(200);
        $response->assertSee('REQ-20261013-0001');
        $response->assertSee('Disetujui');
        $response->assertSee('Proyek Pemohon');
        $response->assertSee('Rincian Item PO Lapangan');
        $response->assertSee('Semen Gresik 40kg');
    }

    public function test_edit_page_renders_successfully(): void
    {
        $mr = MaterialRequest::create([
            'request_number' => 'REQ-20261013-0002',
            'from_warehouse_id' => null,
            'to_warehouse_id' => $this->projectWarehouse->id,
            'requested_by_user_id' => $this->user->id,
            'status' => 'draft',
            'needed_at' => now()->addDays(2),
        ]);

        $response = $this->actingAs($this->user)->get(route('material-requests.edit', $mr));
        $response->assertStatus(200);
        $response->assertSee('Edit PO Lapangan');
        $response->assertSee('Simpan Perubahan PO Lapangan');
    }
}
