<?php

namespace Tests\Feature;

use App\Models\Material;
use App\Models\MaterialRequest;
use App\Models\MaterialRequestItem;
use App\Models\Project;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PoLapanganLabelTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminPusat;
    protected User $adminProyek;
    protected Warehouse $centralWh;
    protected Warehouse $projectWh;
    protected Project $project;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->adminPusat = User::where('email', 'admin.pusat@arsikon.co.id')->firstOrFail();
        $this->adminProyek = User::where('email', 'admin.proyek1@arsikon.co.id')->firstOrFail();
        $this->centralWh = Warehouse::where('is_central', true)->firstOrFail();

        $this->project = Project::create([
            'name' => 'Proyek Gedung Tower A',
            'code' => 'PRJ-TWR-A',
            'status' => 'active',
        ]);

        $this->projectWh = Warehouse::create([
            'name' => 'Gudang Site Tower A',
            'code' => 'WH-TWR-A',
            'type' => 'project',
            'is_central' => false,
            'is_active' => true,
            'project_id' => $this->project->id,
        ]);

        $this->adminProyek->warehouses()->attach($this->projectWh->id);
    }

    public function test_material_request_index_shows_po_lapangan_and_does_not_contain_permintaan_material(): void
    {
        $response = $this->actingAs($this->adminPusat)->get(route('material-requests.index'));

        $response->assertStatus(200);
        $response->assertSee('PO Lapangan');
        $response->assertDontSee('Permintaan Material');
    }

    public function test_material_request_create_shows_po_lapangan_and_does_not_contain_permintaan_material(): void
    {
        $response = $this->actingAs($this->adminProyek)->get(route('material-requests.create'));

        $response->assertStatus(200);
        $response->assertSee('PO Lapangan');
        $response->assertDontSee('Permintaan Material');
    }

    public function test_material_request_show_shows_po_lapangan_and_warehouse_project_display_format(): void
    {
        $mr = MaterialRequest::create([
            'request_number' => 'MR-2026-0001',
            'from_warehouse_id' => $this->centralWh->id,
            'to_warehouse_id' => $this->projectWh->id,
            'requested_by_user_id' => $this->adminProyek->id,
            'status' => 'submitted',
            'notes' => 'Keperluan lantai 3',
        ]);

        $response = $this->actingAs($this->adminPusat)->get(route('material-requests.show', $mr));

        $response->assertStatus(200);
        $response->assertSee('PO Lapangan');
        $response->assertDontSee('Permintaan Material');
        // Harus menampilkan nama proyek untuk gudang tujuan yang terhubung proyek
        $response->assertSee('Proyek Gedung Tower A');
        // Label untuk gudang proyek di detail info card
        $response->assertSee('Nama Proyek');
    }

    public function test_old_route_names_remain_intact_and_callable(): void
    {
        $this->assertTrue(route('material-requests.index') !== '');
        $this->assertTrue(route('material-requests.create') !== '');
        $this->assertTrue(route('material-requests.store') !== '');
    }
}
