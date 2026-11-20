<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectMaterialTransfer;
use App\Models\ProjectMaterialTransferItem;
use App\Models\ProjectToolRental;
use App\Models\ProjectToolRentalLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProjectToolRentalAndTransferTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'Admin Pengadaan', 'guard_name' => 'web']);

        $this->user = User::factory()->create();
        $this->user->assignRole('Admin Pengadaan');

        $this->project = Project::create([
            'code'                => 'PRJ-FAP-TEST',
            'name'                => 'Proyek Faperta Test',
            'location'            => 'Yogyakarta',
            'status'              => 'active',
            'operational_balance' => 50000000.00,
        ]);
    }

    public function test_can_view_project_notes_with_transfers_and_rentals()
    {
        $rental = ProjectToolRental::create([
            'project_id'      => $this->project->id,
            'tool_name'       => 'EXCAVATOR 78 08',
            'vendor_name'     => 'Tibo Urip Mulyo',
            'docket_number'   => 'TU-EXCA-01',
            'start_date'      => '2026-08-01',
            'rental_duration' => 60,
            'duration_unit'   => 'Jam',
            'rate_per_unit'   => 175000,
            'status'          => 'active',
            'created_by'      => $this->user->id,
        ]);

        $transfer = ProjectMaterialTransfer::create([
            'project_id'            => $this->project->id,
            'project_name'          => $this->project->name,
            'title'                 => 'SURAT JALAN FAPERTA',
            'origin_location'       => 'Gudang Utama',
            'destination_location'  => 'Proyek Faperta UGM',
            'transfer_date'         => '2026-08-28',
            'docket_number'         => 'SJ-FAP-01',
            'driver_or_transporter' => 'Budi / Dump Truck',
            'status'                => 'in_transit',
            'created_by'            => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('project-notes.index', ['project_id' => $this->project->id]));

        $response->assertStatus(200);
        $response->assertSee('EXCAVATOR 78 08');
        $response->assertSee('SURAT JALAN FAPERTA');
        $response->assertSee('Semua Panel (Split View)');
        $response->assertSee('3. Mobilisasi', false);
    }

    public function test_can_manage_rental_timesheet_logs()
    {
        $rental = ProjectToolRental::create([
            'project_id'      => $this->project->id,
            'tool_name'       => 'EXCA 78 08 TIBO URIP',
            'vendor_name'     => 'Tibo Urip Mulyo',
            'start_date'      => '2026-08-01',
            'rental_duration' => 0,
            'duration_unit'   => 'Jam',
            'rate_per_unit'   => 175000,
            'status'          => 'active',
            'created_by'      => $this->user->id,
        ]);

        // 1. View rental show page
        $showRes = $this->actingAs($this->user)
            ->get(route('project-notes.rentals.show', $rental));
        $showRes->assertStatus(200);
        $showRes->assertSee('Logbook Harian Pemakaian Alat Kerja');

        // 2. Store a daily log
        $storeRes = $this->actingAs($this->user)
            ->post(route('project-notes.rental-logs.store', $rental), [
                'usage_date'     => '2026-08-01',
                'usage_duration' => 7.5,
                'duration_unit'  => 'Jam',
                'activity_type'  => 'Layani boredpile',
                'equipment_spec' => 'Bucket 0.3m3',
                'notes'          => 'Lancar',
            ]);
        $storeRes->assertSessionHas('success');

        $rental->refresh();
        $this->assertEquals(7.5, (float)$rental->rental_duration);
        $this->assertEquals(7.5 * 175000, (float)$rental->total_rental_cost);

        $log = $rental->logs()->first();
        $this->assertNotNull($log);
        $this->assertEquals('Layani boredpile', $log->activity_type);

        // 3. Delete log
        $delRes = $this->actingAs($this->user)
            ->delete(route('project-notes.rental-logs.destroy', $log));
        $delRes->assertSessionHas('success');

        $rental->refresh();
        $this->assertEquals(0, (float)$rental->rental_duration);
    }

    public function test_can_manage_material_transfer_and_items()
    {
        $transfer = ProjectMaterialTransfer::create([
            'project_id'            => $this->project->id,
            'project_name'          => $this->project->name,
            'title'                 => 'MOBILISASI LOGISTIK FAPERTA',
            'origin_location'       => 'Workshop Arsikon',
            'destination_location'  => 'Site Faperta UGM',
            'transfer_date'         => '2026-08-28',
            'docket_number'         => 'SJ-FAP-002',
            'status'                => 'in_transit',
            'created_by'            => $this->user->id,
        ]);

        // 1. View transfer show page
        $showRes = $this->actingAs($this->user)
            ->get(route('project-notes.transfers.show', $transfer));
        $showRes->assertStatus(200);
        $showRes->assertSee('MOBILISASI LOGISTIK FAPERTA');

        // 2. Store item
        $storeItemRes = $this->actingAs($this->user)
            ->post(route('project-notes.transfer-items.store', $transfer), [
                'item_name'     => 'Balok Beton 30x40',
                'quantity'      => 8,
                'unit'          => 'Unit',
                'specification' => 'K-350 Mutu Tinggi',
                'item_type'     => 'material',
                'condition'     => 'good',
                'notes'         => 'Muat truk 1',
            ]);
        $storeItemRes->assertSessionHas('success');

        $this->assertDatabaseHas('project_material_transfer_items', [
            'project_material_transfer_id' => $transfer->id,
            'item_name'                    => 'Balok Beton 30x40',
            'quantity'                     => 8,
        ]);

        $item = $transfer->items()->first();
        $this->assertNotNull($item);

        // 3. Delete item
        $delItemRes = $this->actingAs($this->user)
            ->delete(route('project-notes.transfer-items.destroy', $item));
        $delItemRes->assertSessionHas('success');

        $this->assertDatabaseMissing('project_material_transfer_items', [
            'id' => $item->id,
        ]);
    }
}
