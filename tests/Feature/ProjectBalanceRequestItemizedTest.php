<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectBalanceLog;
use App\Models\ProjectBalanceRequest;
use App\Models\ProjectBalanceRequestItem;
use App\Models\User;
use App\Models\Warehouse;
use Database\Seeders\MasterDataSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectBalanceRequestItemizedTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminPusat;
    protected User $adminPoProyek;
    protected Project $project;
    protected Warehouse $warehouse;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(MasterDataSeeder::class);

        $this->adminPusat = User::firstOrCreate(
            ['email' => 'admin.pusat.test@arsikon.com'],
            ['name' => 'Admin Pusat Testing', 'password' => bcrypt('Password123!')]
        );
        $this->adminPusat->syncRoles(['Admin Pusat']);

        $this->project = Project::firstOrCreate(
            ['code' => 'PRJ-TEST-PKL'],
            ['name' => 'Proyek Gedung Testing PKL', 'operational_balance' => 500000.00]
        );

        $this->warehouse = Warehouse::firstOrCreate(
            ['code' => 'WH-TEST-PKL'],
            [
                'name'       => 'Gudang Testing PKL',
                'project_id' => $this->project->id,
                'is_central' => false,
            ]
        );

        $this->adminPoProyek = User::firstOrCreate(
            ['email' => 'po.proyek.test@arsikon.com'],
            ['name' => 'Admin PO Proyek Testing', 'password' => bcrypt('Password123!')]
        );
        $this->adminPoProyek->syncRoles(['Admin PO Proyek']);
        $this->adminPoProyek->warehouses()->sync([$this->warehouse->id]);
    }

    public function test_can_submit_itemized_pengajuan_kas_lapangan(): void
    {
        $payload = [
            'project_id'          => $this->project->id,
            'request_date'        => '2026-10-28',
            'purpose'             => 'Belanja material darurat dan konsumsi lembur',
            'urgency'             => 'urgent',
            'signer_requester'    => 'Budi Pelaksana Lapangan',
            'signer_site_manager' => 'Ir. Hartono Site Manager',
            'items'               => [
                [
                    'item_name'  => 'Paku 5cm',
                    'quantity'   => 2,
                    'unit'       => 'dus',
                    'unit_price' => 50000,
                    'notes'      => 'Kebutuhan bekisting',
                ],
                [
                    'item_name'  => 'Semen Portland 50kg',
                    'quantity'   => 5,
                    'unit'       => 'sak',
                    'unit_price' => 65000,
                    'notes'      => 'Darurat pengecoran sloof',
                ],
            ],
        ];

        $response = $this->actingAs($this->adminPoProyek)
            ->post(route('payments.requestBalance'), $payload);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        // Total: (2 * 50.000) + (5 * 65.000) = 100.000 + 325.000 = 425.000
        $this->assertDatabaseHas('project_balance_requests', [
            'project_id'          => $this->project->id,
            'requested_by'        => $this->adminPoProyek->id,
            'amount'              => 425000.00,
            'urgency'             => 'urgent',
            'status'              => 'pending',
            'signer_requester'    => 'Budi Pelaksana Lapangan',
            'signer_site_manager' => 'Ir. Hartono Site Manager',
        ]);

        $balanceRequest = ProjectBalanceRequest::where('project_id', $this->project->id)->first();
        $this->assertNotNull($balanceRequest);
        $this->assertStringStartsWith('PKL-', $balanceRequest->request_number);
        $this->assertCount(2, $balanceRequest->items);

        $this->assertDatabaseHas('project_balance_request_items', [
            'project_balance_request_id' => $balanceRequest->id,
            'item_name'                  => 'Paku 5cm',
            'quantity'                   => 2.00,
            'unit'                       => 'dus',
            'unit_price'                 => 50000.00,
            'total_price'                => 100000.00,
        ]);

        $this->assertDatabaseHas('project_balance_request_items', [
            'project_balance_request_id' => $balanceRequest->id,
            'item_name'                  => 'Semen Portland 50kg',
            'quantity'                   => 5.00,
            'unit'                       => 'sak',
            'unit_price'                 => 65000.00,
            'total_price'                => 325000.00,
        ]);
    }

    public function test_can_submit_simplified_pengajuan_kas_lapangan_without_urgency_purpose_notes_and_auto_assigns_project(): void
    {
        // Payload minimal sesuai form baru: tanpa project_id (auto-assign), tanpa urgency, tanpa purpose, tanpa item notes
        $payload = [
            'request_date'        => '2026-10-29',
            'signer_requester'    => 'Pelaksana Proyek Mandiri',
            'signer_site_manager' => 'Site Manager Lapangan',
            'items'               => [
                [
                    'item_name'  => 'Triplek 9mm Cor',
                    'quantity'   => 10,
                    'unit'       => 'lbr',
                    'unit_price' => 85000,
                ],
                [
                    'item_name'  => 'Kawat Ikat Bendrat',
                    'quantity'   => 5,
                    'unit'       => 'kg',
                    'unit_price' => 22000,
                ],
            ],
        ];

        $response = $this->actingAs($this->adminPoProyek)
            ->post(route('payments.requestBalance'), $payload);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        // Total: (10 * 85.000) + (5 * 22.000) = 850.000 + 110.000 = 960.000
        $this->assertDatabaseHas('project_balance_requests', [
            'project_id'          => $this->project->id, // auto fallback to user's assigned project
            'requested_by'        => $this->adminPoProyek->id,
            'amount'              => 960000.00,
            'urgency'             => 'normal', // auto fallback
            'purpose'             => 'Pengajuan Kebutuhan Kas Lapangan (2 item barang)', // auto generated
            'status'              => 'pending',
            'signer_requester'    => 'Pelaksana Proyek Mandiri',
            'signer_site_manager' => 'Site Manager Lapangan',
        ]);

        $balanceRequest = ProjectBalanceRequest::where('project_id', $this->project->id)
            ->where('amount', 960000.00)
            ->first();
        $this->assertNotNull($balanceRequest);
        $this->assertCount(2, $balanceRequest->items);

        $this->assertDatabaseHas('project_balance_request_items', [
            'project_balance_request_id' => $balanceRequest->id,
            'item_name'                  => 'Triplek 9mm Cor',
            'quantity'                   => 10.00,
            'unit'                       => 'lbr',
            'unit_price'                 => 85000.00,
            'total_price'                => 850000.00,
            'notes'                      => null,
        ]);
    }

    public function test_can_view_and_print_pengajuan_kas_lapangan(): void
    {
        $balanceRequest = ProjectBalanceRequest::create([
            'project_id'          => $this->project->id,
            'requested_by'        => $this->adminPoProyek->id,
            'request_date'        => '2026-10-28',
            'amount'              => 200000.00,
            'purpose'             => 'Kebutuhan perlengkapan APD dan P3K',
            'urgency'             => 'normal',
            'signer_requester'    => 'Joko Admin Proyek',
            'signer_site_manager' => 'Dwi Mandor Proyek',
            'status'              => 'pending',
        ]);

        $balanceRequest->items()->create([
            'item_name'   => 'Rompi Safety K3',
            'quantity'    => 4,
            'unit'        => 'pcs',
            'unit_price'  => 50000,
            'total_price' => 200000,
        ]);

        $response = $this->actingAs($this->adminPoProyek)
            ->get(route('payments.balance-requests.print', $balanceRequest));

        $response->assertOk();
        $response->assertSee('PENGAJUAN KAS LAPANGAN');
        $response->assertSee($balanceRequest->request_number);
        $response->assertSee('Rompi Safety K3');
        $response->assertSee('200.000');
        $response->assertSee('Joko Admin Proyek');
        $response->assertSee('Dwi Mandor Proyek');
        $response->assertSee('Yang Mengajukan');
        $response->assertSee('Mengetahui / Diperiksa');
        $response->assertSee('Disetujui Oleh');
    }

    public function test_approving_pengajuan_kas_lapangan_increases_balance_and_records_audit(): void
    {
        $initialBalance = (float) $this->project->operational_balance;

        $balanceRequest = ProjectBalanceRequest::create([
            'project_id'          => $this->project->id,
            'requested_by'        => $this->adminPoProyek->id,
            'request_date'        => '2026-10-28',
            'amount'              => 300000.00,
            'purpose'             => 'Belanja kawat bendrat',
            'urgency'             => 'normal',
            'status'              => 'pending',
        ]);

        $response = $this->actingAs($this->adminPusat)
            ->post(route('payments.approveBalanceRequest', $balanceRequest), [
                'reference_number' => 'BKK-PUSAT-0099',
                'notes'            => 'Disetujui penuh via transfer bank',
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $balanceRequest->refresh();
        $this->assertEquals('approved', $balanceRequest->status);
        $this->assertEquals($this->adminPusat->id, $balanceRequest->approved_by);
        $this->assertEquals('BKK-PUSAT-0099', $balanceRequest->reference_number);

        $this->project->refresh();
        $this->assertEquals($initialBalance + 300000.00, (float) $this->project->operational_balance);

        $this->assertDatabaseHas('project_balance_logs', [
            'project_id'       => $this->project->id,
            'type'             => 'topup',
            'amount'           => 300000.00,
            'balance_after'    => $initialBalance + 300000.00,
            'reference_number' => 'BKK-PUSAT-0099',
        ]);
    }

    public function test_approving_pengajuan_kas_lapangan_without_payload_succeeds_with_default_reference_number(): void
    {
        $initialBalance = (float) $this->project->operational_balance;

        $balanceRequest = ProjectBalanceRequest::create([
            'project_id'          => $this->project->id,
            'requested_by'        => $this->adminPoProyek->id,
            'request_date'        => '2026-10-29',
            'amount'              => 150000.00,
            'purpose'             => 'Kebutuhan paku dan kawat',
            'urgency'             => 'normal',
            'status'              => 'pending',
        ]);

        // POST tanpa reference_number & tanpa notes (seperti klik tombol Setujui langsung di modal/tabel)
        $response = $this->actingAs($this->adminPusat)
            ->post(route('payments.approveBalanceRequest', $balanceRequest), []);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $balanceRequest->refresh();
        $this->assertEquals('approved', $balanceRequest->status);
        $this->assertEquals("REQ-BAL-{$balanceRequest->id}", $balanceRequest->reference_number);

        $this->project->refresh();
        $this->assertEquals($initialBalance + 150000.00, (float) $this->project->operational_balance);
    }
}

