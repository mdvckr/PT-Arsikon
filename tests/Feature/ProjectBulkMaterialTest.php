<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectBulkMaterial;
use App\Models\ProjectBulkMaterialItem;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProjectBulkMaterialTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Project $project;
    protected Supplier $supplier;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'Admin PO', 'guard_name' => 'web']);
        $this->user = User::factory()->create();
        $this->user->assignRole('Admin PO');

        $this->project = Project::create([
            'code'                => 'PRJ-TEST-01',
            'name'                => 'Proyek Gedung UGM',
            'location'            => 'Yogyakarta',
            'status'              => 'active',
            'operational_balance' => 50000000.00,
        ]);

        $this->supplier = Supplier::create([
            'name'  => 'Supri',
            'phone' => '08123456789',
        ]);
    }

    public function test_can_view_bulk_materials_in_project_notes_index()
    {
        $bulk = ProjectBulkMaterial::create([
            'project_id'    => $this->project->id,
            'title'         => 'CATATAN PEMBELIAN PASIR & BATU (SUPRI)',
            'supplier_name' => 'Supri',
            'start_date'    => '2026-10-11',
            'end_date'      => '2026-11-06',
            'status'        => 'active',
            'created_by'    => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('project-notes.index', ['project_id' => $this->project->id]));

        $response->assertStatus(200);
        $response->assertSee('CATATAN PEMBELIAN PASIR & BATU (SUPRI)');
        $response->assertSee('2. Material Curah (Pasir / Batu)');
    }

    public function test_can_create_bulk_material()
    {
        $response = $this->actingAs($this->user)
            ->post(route('project-notes.bulk.store'), [
                'project_id'    => $this->project->id,
                'title'         => 'CATATAN PASIR & BATU PAK SUPRI',
                'supplier_name' => 'Supri',
                'start_date'    => '2026-10-11',
                'end_date'      => '2026-11-06',
                'status'        => 'active',
                'notes'         => 'Material urug dan cor',
            ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('project_bulk_materials', [
            'title'         => 'CATATAN PASIR & BATU PAK SUPRI',
            'supplier_name' => 'Supri',
            'project_id'    => $this->project->id,
        ]);
    }

    public function test_can_add_bulk_material_item()
    {
        $bulk = ProjectBulkMaterial::create([
            'project_id'    => $this->project->id,
            'title'         => 'CATATAN PEMBELIAN PASIR & BATU',
            'supplier_name' => 'Supri',
            'start_date'    => '2026-10-11',
            'status'        => 'active',
            'created_by'    => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('project-notes.bulk-items.store', $bulk), [
                'delivery_date' => '2026-10-11',
                'ticket_number' => 'N-001',
                'quantity'      => 2,
                'unit'          => 'Rit',
                'material_type' => 'Pasir',
                'unit_price'    => 250000,
                'notes'         => 'Pasir cor kali progo',
            ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('project_bulk_material_items', [
            'project_bulk_material_id' => $bulk->id,
            'material_type'            => 'Pasir',
            'quantity'                 => 2,
            'subtotal'                 => 500000,
        ]);
    }

    public function test_can_view_bulk_material_detail_and_print()
    {
        $bulk = ProjectBulkMaterial::create([
            'project_id'    => $this->project->id,
            'title'         => 'CATATAN PEMBELIAN PASIR & BATU',
            'supplier_name' => 'Supri',
            'start_date'    => '2026-10-11',
            'status'        => 'active',
            'created_by'    => $this->user->id,
        ]);

        $bulk->items()->create([
            'delivery_date' => '2026-10-11',
            'quantity'      => 1,
            'unit'          => 'Rit',
            'material_type' => 'Pasir',
            'created_by'    => $this->user->id,
        ]);

        $showResponse = $this->actingAs($this->user)
            ->get(route('project-notes.bulk.show', $bulk));
        $showResponse->assertStatus(200);
        $showResponse->assertSee('CATATAN PEMBELIAN PASIR & BATU');
        $showResponse->assertSee('Pasir');

        $printResponse = $this->actingAs($this->user)
            ->get(route('project-notes.bulk.print', $bulk));
        $printResponse->assertStatus(200);
        $printResponse->assertSee('PT. ARSIKON CIPTA KARYA');
    }

    public function test_can_export_bulk_material_excel()
    {
        Excel::fake();

        $bulk = ProjectBulkMaterial::create([
            'project_id'    => $this->project->id,
            'title'         => 'CATATAN PEMBELIAN PASIR & BATU',
            'supplier_name' => 'Supri',
            'start_date'    => '2026-10-11',
            'status'        => 'active',
            'created_by'    => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('project-notes.bulk.export', $bulk));

        $response->assertStatus(200);
        Excel::assertDownloaded("Catatan-Curah-{$bulk->id}-catatan-pembelian-pasir-batu.xlsx");
    }
}
