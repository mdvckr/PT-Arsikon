<?php

namespace Tests\Feature;

use App\Exports\WeeklyMaterialAndRentalExport;
use App\Models\MaterialLedger;
use App\Models\MaterialLedgerEntry;
use App\Models\Project;
use App\Models\ProjectToolRental;
use App\Models\Supplier;
use App\Models\User;
use App\Services\MaterialLedgerService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProjectMaterialNoteTest extends TestCase
{
    use RefreshDatabase;

    protected User $userProyek;
    protected User $userPusat;
    protected Project $project;
    protected Supplier $supplier;

    protected function setUp(): void
    {
        parent::setUp();

        $roleProyek = Role::firstOrCreate(['name' => 'Admin PO Proyek', 'guard_name' => 'web']);
        $rolePusat  = Role::firstOrCreate(['name' => 'Admin PO', 'guard_name' => 'web']);

        $this->userProyek = User::factory()->create();
        $this->userProyek->assignRole('Admin PO Proyek');

        $this->userPusat = User::factory()->create();
        $this->userPusat->assignRole('Admin PO');

        $this->project = Project::create([
            'code'                => 'PRJ-UGM-TEST',
            'name'                => 'Fak. Teknik UGM Test',
            'location'            => 'Yogyakarta',
            'status'              => 'active',
            'operational_balance' => 50000000.00,
        ]);

        $this->supplier = Supplier::create([
            'name'  => 'PT. Solusi Bangun Beton (Dynamix)',
            'phone' => '0274-123456',
        ]);
    }

    public function test_user_can_view_project_notes_index()
    {
        $response = $this->actingAs($this->userProyek)
            ->get(route('project-notes.index', ['project_id' => $this->project->id]));

        $response->assertStatus(200);
        $response->assertSee('Catatan Material');
        $response->assertSee('Sewa Alat');
    }

    public function test_can_view_show_ledger_page()
    {
        $ledger = MaterialLedger::create([
            'project_id'         => $this->project->id,
            'title'              => 'CATATAN COR READYMIX SHOW TEST',
            'supplier_name'      => 'PT. Solusi Bangun Beton',
            'account_behalf'     => 'PBAG',
            'default_unit'       => 'm3',
            'default_unit_price' => 894438,
            'start_date'         => '2026-09-22',
            'created_by'         => $this->userProyek->id,
        ]);

        $response = $this->actingAs($this->userProyek)
            ->get(route('project-notes.ledgers.show', $ledger));

        $response->assertStatus(200);
        $response->assertSee('CATATAN COR READYMIX SHOW TEST');
        $response->assertSee('Vol. Deposit');
        $response->assertSee('Vol Pengiriman');
    }

    public function test_material_ledger_service_calculates_running_totals_correctly()
    {
        $ledger = MaterialLedger::create([
            'project_id'         => $this->project->id,
            'title'              => 'CATATAN COR READYMIX UGM TEST',
            'supplier_id'        => $this->supplier->id,
            'supplier_name'      => $this->supplier->name,
            'account_behalf'     => 'PBAG',
            'default_unit'       => 'm3',
            'default_unit_price' => 894438,
            'week_number'        => 38,
            'year'               => 2026,
            'start_date'         => '2026-09-22',
            'end_date'           => '2026-09-28',
            'created_by'         => $this->userProyek->id,
        ]);

        // Entry 1: 5 m3 (T1)
        $ledger->entries()->create([
            'delivery_date'          => '2026-09-22',
            'delivery_note_number'   => '2290102',
            'material_specification' => 'K-300 T1',
            'deposit_volume'         => null,
            'delivery_volume'        => 5.0,
            'unit'                   => 'm3',
            'unit_price'             => 894438,
            'amount_used'            => 4472190,
            'batch_color'            => 'green',
            'sort_order'             => 1,
            'created_by'             => $this->userProyek->id,
        ]);

        // Entry 2: 6 m3 (T2), Deposit 11 m3
        $ledger->entries()->create([
            'delivery_date'          => '2026-09-22',
            'delivery_note_number'   => '2290103',
            'material_specification' => 'K-300 T2',
            'deposit_volume'         => 11.0,
            'delivery_volume'        => 6.0,
            'unit'                   => 'm3',
            'unit_price'             => 894438,
            'amount_used'            => 5366628,
            'batch_color'            => 'green',
            'sort_order'             => 2,
            'created_by'             => $this->userProyek->id,
        ]);

        $service = new MaterialLedgerService();
        $calculated = $service->getCalculatedEntries($ledger);

        $this->assertCount(2, $calculated);

        // Baris 1: running volume 5, running amount 4.472.190
        $this->assertEquals(5.0, $calculated[0]->running_total_volume);
        $this->assertEquals(4472190.0, $calculated[0]->running_total_amount);

        // Baris 2: running volume 11, running amount 9.838.818, sisa vol deposit 0
        $this->assertEquals(11.0, $calculated[1]->running_total_volume);
        $this->assertEquals(9838818.0, $calculated[1]->running_total_amount);
        $this->assertEquals(0.0, $calculated[1]->remaining_deposit_vol);

        // Sync summary
        $service->syncLedgerSummary($ledger);
        $ledger->refresh();

        $this->assertEquals(11.0, (float)$ledger->total_deposit_vol);
        $this->assertEquals(11.0, (float)$ledger->total_delivered_vol);
        $this->assertEquals(0.0, (float)$ledger->remaining_deposit_vol);
        $this->assertEquals(9838818.0, (float)$ledger->total_used_amount);
    }

    public function test_can_create_material_ledger_and_entry_via_http()
    {
        // 1. Store Ledger
        $response = $this->actingAs($this->userProyek)->post(route('project-notes.ledgers.store'), [
            'project_id'         => $this->project->id,
            'title'              => 'Catatan Semen Curah Proyek',
            'supplier_name'      => 'PT. Semen Indonesia',
            'account_behalf'     => 'PBAG',
            'default_unit'       => 'sak',
            'default_unit_price' => 65000,
            'start_date'         => '2026-09-22',
            'end_date'           => '2026-09-28',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('material_ledgers', [
            'title' => 'Catatan Semen Curah Proyek',
            'account_behalf' => 'PBAG',
        ]);

        $ledger = MaterialLedger::where('title', 'Catatan Semen Curah Proyek')->first();

        // 2. Store Entry
        $entryResponse = $this->actingAs($this->userProyek)->post(route('project-notes.entries.store', $ledger), [
            'delivery_date'          => '2026-09-23',
            'delivery_note_number'   => 'SJ-SMN-01',
            'material_specification' => 'Semen Gresik 40kg',
            'deposit_volume'         => 100,
            'delivery_volume'        => 50,
            'unit'                   => 'sak',
            'unit_price'             => 65000,
            'batch_color'            => 'green',
        ]);

        $entryResponse->assertSessionHasNoErrors();
        $this->assertDatabaseHas('material_ledger_entries', [
            'delivery_note_number' => 'SJ-SMN-01',
            'delivery_volume'      => 50,
        ]);
    }

    public function test_can_create_records_with_empty_price_fields()
    {
        // 1. Ledger without default price
        $response = $this->actingAs($this->userProyek)->post(route('project-notes.ledgers.store'), [
            'project_id'         => $this->project->id,
            'title'              => 'Catatan Pasir Merapi Tanpa Harga',
            'supplier_name'      => 'TB Pasir Jaya',
            'default_unit'       => 'rit',
            'start_date'         => '2026-09-22',
        ]);

        $response->assertSessionHasNoErrors();
        $ledger = MaterialLedger::where('title', 'Catatan Pasir Merapi Tanpa Harga')->first();
        $this->assertEquals(0, (float)$ledger->default_unit_price);

        // 2. Entry without price
        $entryResponse = $this->actingAs($this->userProyek)->post(route('project-notes.entries.store', $ledger), [
            'delivery_date'          => '2026-09-23',
            'delivery_note_number'   => 'SJ-PSR-01',
            'material_specification' => 'Pasir Merapi Dump Truck',
            'delivery_volume'        => 3,
            'unit'                   => 'rit',
            'batch_color'            => 'green',
        ]);

        $entryResponse->assertSessionHasNoErrors();
        $entry = MaterialLedgerEntry::where('delivery_note_number', 'SJ-PSR-01')->first();
        $this->assertEquals(0, (float)$entry->unit_price);
        $this->assertEquals(0, (float)$entry->amount_used);

        // 3. Rental without price
        $rentalResponse = $this->actingAs($this->userProyek)->post(route('project-notes.rentals.store'), [
            'project_id'      => $this->project->id,
            'tool_name'       => 'Stamper Kuda Tanpa Harga',
            'vendor_name'     => 'Rental Mandiri',
            'start_date'      => '2026-09-22',
            'rental_duration' => 1,
            'duration_unit'   => 'Hari',
            'status'          => 'active',
        ]);

        $rentalResponse->assertSessionHasNoErrors();
        $rental = ProjectToolRental::where('tool_name', 'Stamper Kuda Tanpa Harga')->first();
        $this->assertEquals(0, (float)$rental->rate_per_unit);
        $this->assertEquals(0, (float)$rental->total_rental_cost);
    }

    public function test_can_create_and_delete_tool_rental_via_http()
    {
        $storeResponse = $this->actingAs($this->userProyek)->post(route('project-notes.rentals.store'), [
            'project_id'      => $this->project->id,
            'tool_name'       => 'Concrete Pump Standar Test',
            'vendor_name'     => 'PT. Pompa Beton Perkasa',
            'start_date'      => '2026-09-22',
            'rental_duration' => 2,
            'duration_unit'   => 'Hari',
            'rate_per_unit'   => 1500000,
            'deposit_amount'  => 500000,
            'status'          => 'active',
        ]);

        $storeResponse->assertSessionHasNoErrors();
        $this->assertDatabaseHas('project_tool_rentals', [
            'tool_name'         => 'Concrete Pump Standar Test',
            'total_rental_cost' => 3500000, // (2 * 1.500.000) + 500.000
        ]);

        $rental = ProjectToolRental::where('tool_name', 'Concrete Pump Standar Test')->first();

        $deleteResponse = $this->actingAs($this->userProyek)->delete(route('project-notes.rentals.destroy', $rental));
        $deleteResponse->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('project_tool_rentals', ['id' => $rental->id]);
    }

    public function test_can_export_weekly_material_and_tool_to_excel()
    {
        $response = $this->actingAs($this->userPusat)->get(route('project-notes.export-weekly', [
            'project_id' => $this->project->id,
            'start_date' => '2026-09-22',
            'end_date'   => '2026-09-28',
        ]));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertStringContainsString('attachment;', $response->headers->get('content-disposition'));
        $this->assertStringContainsString('.xlsx', $response->headers->get('content-disposition'));

        $export = new WeeklyMaterialAndRentalExport(collect(), collect(), $this->project);
        $sheets = $export->sheets();
        $this->assertCount(2, $sheets);
        $this->assertEquals('Catatan Material', $sheets[0]->title());
        $this->assertEquals('Sewa Alat Proyek', $sheets[1]->title());
    }

    public function test_can_export_single_material_ledger_to_excel()
    {
        $ledger = MaterialLedger::create([
            'project_id'         => $this->project->id,
            'title'              => 'Catatan Readymix Single Export Test',
            'supplier_name'      => 'PT. Solusi Bangun Beton',
            'default_unit'       => 'm3',
            'default_unit_price' => 894438,
            'start_date'         => '2026-09-22',
            'created_by'         => $this->userProyek->id,
        ]);

        $response = $this->actingAs($this->userProyek)
            ->get(route('project-notes.ledgers.export', $ledger));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertStringContainsString('attachment;', $response->headers->get('content-disposition'));
        $this->assertStringContainsString('.xlsx', $response->headers->get('content-disposition'));
    }

    public function test_can_print_single_material_ledger()
    {
        $ledger = MaterialLedger::create([
            'project_id'         => $this->project->id,
            'title'              => 'Catatan Readymix Single Print Test',
            'supplier_name'      => 'PT. Solusi Bangun Beton',
            'default_unit'       => 'm3',
            'default_unit_price' => 894438,
            'start_date'         => '2026-09-22',
            'created_by'         => $this->userProyek->id,
        ]);

        $response = $this->actingAs($this->userProyek)
            ->get(route('project-notes.ledgers.print', $ledger));

        $response->assertOk();
        $response->assertSee('REKAPITULASI CATATAN MATERIAL PROYEK');
        $response->assertSee('Catatan Readymix Single Print Test');
        $response->assertSee('Mengetahui Suplier');
    }

    public function test_can_export_and_print_tool_rentals()
    {
        ProjectToolRental::create([
            'project_id'        => $this->project->id,
            'tool_name'         => 'Vibrator Test Export',
            'vendor_name'       => 'Sewa Mulia',
            'start_date'        => '2026-09-22',
            'rental_duration'   => 2,
            'duration_unit'     => 'Hari',
            'rate_per_unit'     => 100000,
            'total_rental_cost' => 200000,
            'status'            => 'active',
            'created_by'        => $this->userProyek->id,
        ]);

        // 1/1 Export Sewa Alat
        $exportResponse = $this->actingAs($this->userProyek)
            ->get(route('project-notes.export-rentals', ['project_id' => $this->project->id]));
        $exportResponse->assertOk();
        $exportResponse->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertStringContainsString('.xlsx', $exportResponse->headers->get('content-disposition'));

        // 1/1 Print Sewa Alat
        $printResponse = $this->actingAs($this->userProyek)
            ->get(route('project-notes.print-rentals', ['project_id' => $this->project->id]));
        $printResponse->assertOk();
        $printResponse->assertSee('REKAPITULASI SEWA ALAT KERJA PROYEK');
        $printResponse->assertSee('Vibrator Test Export');
        $printResponse->assertSee('Vendor Penyedia Alat');
    }
}
