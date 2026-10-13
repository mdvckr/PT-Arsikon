<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Material;
use App\Models\MaterialRequest;
use App\Models\Project;
use Spatie\Permission\Models\Role;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PoLapanganRequestedAsTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminProyek;
    protected Warehouse $centralWh;
    protected Warehouse $projectWh;
    protected Material $material;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->adminProyek = User::where('email', 'admin.proyek1@arsikon.co.id')->firstOrFail();
        $this->centralWh = Warehouse::where('is_central', true)->firstOrFail();

        $project = Project::create(['name' => 'Proyek Citra', 'code' => 'PRJ-CTR', 'status' => 'active']);
        $this->projectWh = Warehouse::create([
            'name'       => 'Gudang Citra',
            'code'       => 'WH-CTR',
            'type'       => 'project',
            'is_central' => false,
            'is_active'  => true,
            'project_id' => $project->id,
        ]);
        $this->adminProyek->warehouses()->sync([$this->projectWh->id]);

        $cat = Category::first() ?? Category::create(['name' => 'Umum', 'code' => 'UMM', 'type' => 'material']);
        $unit = Unit::first() ?? Unit::create(['name' => 'Pcs', 'code' => 'pcs', 'is_decimal' => false]);

        $this->material = Material::create([
            'name'        => 'Baut Baja 10mm',
            'sku'         => 'MAT-BAUT-10',
            'category_id' => $cat->id,
            'unit_id'     => $unit->id,
            'is_active'   => true,
        ]);
    }

    public function test_requested_as_field_is_saved_and_defaults_to_admin_proyek(): void
    {
        // 1. Submit tanpa requested_as => harus default ke 'Admin Proyek'
        $response1 = $this->actingAs($this->adminProyek)->post(route('material-requests.store'), [
            'warehouse_id'        => $this->projectWh->id,
            'source_warehouse_id' => $this->centralWh->id,
            'needed_at'           => now()->addDays(2)->format('Y-m-d'),
            'quantities'          => [$this->material->id => 10],
        ]);
        $response1->assertSessionHasNoErrors();

        $mr1 = MaterialRequest::latest()->first();
        $this->assertNotNull($mr1);
        $this->assertEquals('Admin Proyek', $mr1->requested_as);

        // 2. Submit dengan opsi 'Site Manager'
        $response2 = $this->actingAs($this->adminProyek)->post(route('material-requests.store'), [
            'warehouse_id'        => $this->projectWh->id,
            'source_warehouse_id' => $this->centralWh->id,
            'needed_at'           => now()->addDays(2)->format('Y-m-d'),
            'requested_as'        => 'Site Manager',
            'quantities'          => [$this->material->id => 15],
        ]);
        $response2->assertSessionHasNoErrors();

        $mr2 = MaterialRequest::where('id', '!=', $mr1->id)->latest()->first();
        $this->assertNotNull($mr2);
        $this->assertEquals('Site Manager', $mr2->requested_as);
    }

    public function test_requested_as_is_displayed_on_show_and_print_pages(): void
    {
        $mr = MaterialRequest::create([
            'request_number'       => 'MR-REQAS-01',
            'from_warehouse_id'    => $this->centralWh->id,
            'to_warehouse_id'      => $this->projectWh->id,
            'requested_by_user_id' => $this->adminProyek->id,
            'requested_as'         => 'Site Engineering',
            'status'               => 'submitted',
            'needed_at'            => now()->addDays(3)->format('Y-m-d'),
        ]);

        // Halaman Detail
        $resShow = $this->actingAs($this->adminProyek)->get(route('material-requests.show', $mr));
        $resShow->assertStatus(200);
        $resShow->assertSee('Site Engineering');
        $resShow->assertSee($this->adminProyek->name);

        // Halaman Print
        $resPrint = $this->actingAs($this->adminProyek)->get(route('material-requests.show', $mr) . '?print=1');
        $resPrint->assertStatus(200);
        $resPrint->assertSee('Site Engineering');
    }

    public function test_no_new_spatie_roles_are_created_in_database(): void
    {
        // Pastikan tidak ada role 'Site Manager' atau 'Site Engineering' yang terbuat di Spatie roles
        $rolesCount = Role::whereIn('name', ['Site Manager', 'Site Engineering'])->count();
        $this->assertEquals(0, $rolesCount, 'Site Manager dan Site Engineering murni metadata, bukan Spatie role.');
    }
}
