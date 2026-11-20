<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectBalanceLog;
use App\Models\ProjectBalanceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProjectBalanceRequestTest extends TestCase
{
    use RefreshDatabase;

    protected User $poProyekUser;
    protected User $poPusatUser;
    protected Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        // Create Permissions
        Permission::firstOrCreate(['name' => 'view balance requests', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'create balance requests', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'approve balance requests', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'reject balance requests', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'view payments', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'create payments', 'guard_name' => 'web']);

        // Create Roles
        $rolePOProyek = Role::firstOrCreate(['name' => 'Admin Pengadaan Proyek', 'guard_name' => 'web']);
        $rolePOProyek->syncPermissions(['view balance requests', 'create balance requests']);

        $rolePOPusat = Role::firstOrCreate(['name' => 'Admin Pengadaan', 'guard_name' => 'web']);
        $rolePOPusat->syncPermissions([
            'view balance requests',
            'create balance requests',
            'approve balance requests',
            'reject balance requests',
            'view payments',
            'create payments',
        ]);

        // Create Project
        $this->project = Project::create([
            'code'                => 'PRJ-FK-01',
            'name'                => 'Proyek Gedung FK Teknik',
            'location'            => 'Yogyakarta',
            'status'              => 'active',
            'operational_balance' => 2000000.00,
        ]);

        // Create Users
        $this->poProyekUser = User::factory()->create([
            'name'  => 'Admin Pembelian Proyek FK Teknik',
            'email' => 'po.proyek@arsikon.co.id',
        ]);
        $this->poProyekUser->assignRole('Admin Pengadaan Proyek');

        $this->poPusatUser = User::factory()->create([
            'name'  => 'Admin Pengadaan Pusat',
            'email' => 'admin.pengadaan@arsikon.co.id',
        ]);
        $this->poPusatUser->assignRole('Admin Pengadaan');
    }

    public function test_po_proyek_can_view_balance_requests_index()
    {
        $response = $this->actingAs($this->poProyekUser)
            ->get(route('project-balance-requests.index', ['project_id' => $this->project->id]));

        $response->assertStatus(200);
        $response->assertSee('Pengajuan Kas Saldo Proyek');
        $response->assertSee('Proyek Gedung FK Teknik');
        $response->assertSee('+ Buat Pengajuan Kas');
    }

    public function test_po_proyek_sidebar_does_not_contain_keuangan_menu()
    {
        $response = $this->actingAs($this->poProyekUser)
            ->get(route('project-balance-requests.index', ['project_id' => $this->project->id]));

        $response->assertStatus(200);
        // Ensure payments.index link is NOT in the sidebar
        $response->assertDontSee(route('payments.index'));
        // But Pengajuan Kas Proyek is present
        $response->assertSee(route('project-balance-requests.index'));
    }

    public function test_po_pusat_is_redirected_to_payments_when_accessing_project_balance_requests()
    {
        $response = $this->actingAs($this->poPusatUser)
            ->get(route('project-balance-requests.index'));

        $response->assertRedirect(route('payments.index'));
        $response->assertSessionHas('info');
    }

    public function test_po_pusat_sidebar_does_not_contain_pengajuan_kas_proyek_menu()
    {
        $response = $this->actingAs($this->poPusatUser)
            ->get(route('payments.index'));

        $response->assertStatus(200);
        // Admin Pengadaan Pusat should see Keuangan
        $response->assertSee(route('payments.index'));
        // But NOT Pengajuan Kas Proyek in the sidebar
        $response->assertDontSee(route('project-balance-requests.index'));
    }

    public function test_po_proyek_can_submit_balance_request_with_items()
    {
        $payload = [
            'project_id'          => $this->project->id,
            'request_date'        => now()->toDateString(),
            'urgency'             => 'normal',
            'purpose'             => 'Pengisian petty cash operasional pembelian semen dan paku',
            'signer_requester'    => 'Admin Pembelian Proyek',
            'signer_site_manager' => 'Ir. Site Manager',
            'items'               => [
                [
                    'item_name' => 'Semen Gresik 40kg (5 sak)',
                    'quantity'  => 5,
                    'unit'      => 'sak',
                    'price'     => 65000,
                    'notes'     => 'Toko Bangunan Berkah',
                ],
                [
                    'item_name' => 'Paku Usuk 7cm (10 kg)',
                    'quantity'  => 10,
                    'unit'      => 'kg',
                    'price'     => 20000,
                    'notes'     => 'Nota kontan',
                ],
            ],
        ];

        $response = $this->actingAs($this->poProyekUser)
            ->post(route('project-balance-requests.store'), $payload);

        $response->assertRedirect(route('project-balance-requests.index', ['project_id' => $this->project->id]));

        $this->assertDatabaseHas('project_balance_requests', [
            'project_id'   => $this->project->id,
            'amount'       => 525000.00,
            'status'       => 'pending',
            'requested_by' => $this->poProyekUser->id,
        ]);

        $this->assertDatabaseHas('project_balance_request_items', [
            'item_name'   => 'Semen Gresik 40kg (5 sak)',
            'quantity'    => 5,
            'total_price' => 325000.00,
        ]);
    }

    public function test_can_render_balance_request_printable_sheet()
    {
        $request = ProjectBalanceRequest::create([
            'request_number'      => 'PKL-202611-0001',
            'request_date'        => now()->toDateString(),
            'project_id'          => $this->project->id,
            'requested_by'        => $this->poProyekUser->id,
            'amount'              => 500000,
            'purpose'             => 'Operasional lapangan',
            'urgency'             => 'urgent',
            'status'              => 'pending',
            'signer_requester'    => 'Admin Pengadaan Proyek',
            'signer_site_manager' => 'Site Manager Lapangan',
            'signer_approver'     => 'Admin Pengadaan Pusat',
        ]);

        $response = $this->actingAs($this->poProyekUser)
            ->get(route('project-balance-requests.print', $request));

        $response->assertStatus(200);
        $response->assertSee('PENGAJUAN KAS LAPANGAN');
        $response->assertSee('PKL-202611-0001');
        $response->assertSee('Admin Pengadaan Pusat');
    }

    public function test_po_pusat_sees_pending_balance_request_in_keuangan_and_approves()
    {
        $request = ProjectBalanceRequest::create([
            'request_number' => 'PKL-202611-0002',
            'request_date'   => now()->toDateString(),
            'project_id'     => $this->project->id,
            'requested_by'   => $this->poProyekUser->id,
            'amount'         => 3000000.00,
            'purpose'        => 'Top up saldo belanja nota harian proyek',
            'urgency'        => 'urgent',
            'status'         => 'pending',
        ]);

        // PO Pusat views Keuangan (payments.index) and sees the incoming pending alert
        $response = $this->actingAs($this->poPusatUser)->get(route('payments.index'));
        $response->assertStatus(200);
        $response->assertSee('Ada 1 Pengajuan Kas Saldo Proyek Menunggu Persetujuan');

        $initialBalance = (float) $this->project->operational_balance;

        // PO Pusat directly approves the request from Keuangan
        $approveResponse = $this->actingAs($this->poPusatUser)
            ->post(route('payments.approveBalanceRequest', $request), [
                'reference_number' => 'TRF-BCA-98765',
            ]);

        $approveResponse->assertRedirect();

        // Verify request updated to approved
        $request->refresh();
        $this->assertEquals('approved', $request->status);
        $this->assertEquals($this->poPusatUser->id, $request->approved_by);
        $this->assertEquals('TRF-BCA-98765', $request->reference_number);

        // Verify project operational balance topped up
        $this->project->refresh();
        $this->assertEquals($initialBalance + 3000000.00, (float) $this->project->operational_balance);

        // Verify balance log recorded
        $this->assertDatabaseHas('project_balance_logs', [
            'project_id' => $this->project->id,
            'type'       => 'topup',
            'amount'     => 3000000.00,
        ]);
    }

    public function test_po_pusat_can_reject_balance_request_from_keuangan()
    {
        $request = ProjectBalanceRequest::create([
            'request_number' => 'PKL-202611-0003',
            'request_date'   => now()->toDateString(),
            'project_id'     => $this->project->id,
            'requested_by'   => $this->poProyekUser->id,
            'amount'         => 10000000.00,
            'purpose'        => 'Pengajuan kas besar',
            'status'         => 'pending',
        ]);

        $response = $this->actingAs($this->poPusatUser)
            ->post(route('payments.rejectBalanceRequest', $request), [
                'rejection_reason' => 'Perincian nota sebelumnya belum dilaporkan secara lengkap.',
            ]);

        $response->assertRedirect();

        $request->refresh();
        $this->assertEquals('rejected', $request->status);
        $this->assertEquals('Perincian nota sebelumnya belum dilaporkan secara lengkap.', $request->rejection_reason);
    }

    public function test_approver_display_name_automatically_follows_po_pusat_or_defaults_to_pengadaan()
    {
        // 1. When PO Pusat user exists in system, automatically uses their name
        $req = new ProjectBalanceRequest(['status' => 'pending']);
        $this->assertEquals($this->poPusatUser->name, $req->approver_display_name);

        // 2. When approved, uses the approver user's name
        $req->approved_by = $this->poPusatUser->id;
        $req->setRelation('approver', $this->poPusatUser);
        $this->assertEquals('Admin Pengadaan Pusat', $req->approver_display_name);

        // 3. Fallback to 'Pengadaan' when no PO Pusat account exists
        User::query()->delete();
        $freshReq = new ProjectBalanceRequest(['status' => 'pending']);
        $this->assertEquals('Pengadaan', $freshReq->approver_display_name);
    }

    public function test_payments_create_is_disabled_and_redirects_to_payments_index()
    {
        $response = $this->actingAs($this->poPusatUser)
            ->get(route('payments.create'));

        $response->assertRedirect(route('payments.index'));
    }

    public function test_can_view_balance_request_show_page()
    {
        $request = ProjectBalanceRequest::create([
            'request_number' => 'PKL-202611-0099',
            'request_date'   => now()->toDateString(),
            'project_id'     => $this->project->id,
            'requested_by'   => $this->poProyekUser->id,
            'amount'         => 1250000.00,
            'purpose'        => 'Pembelian material darurat proyek',
            'urgency'        => 'urgent',
            'status'         => 'pending',
        ]);

        $request->items()->create([
            'item_name'   => 'Semen Darurat',
            'quantity'    => 10,
            'unit'        => 'sak',
            'unit_price'  => 65000,
            'total_price' => 650000,
        ]);

        // PO Pusat views the show page from payments route
        $resPo = $this->actingAs($this->poPusatUser)
            ->get(route('payments.balance-requests.show', $request));

        $resPo->assertOk();
        $resPo->assertSee('PKL-202611-0099');
        $resPo->assertSee('Pembelian material darurat proyek');
        $resPo->assertSee('Semen Darurat');
        $resPo->assertSee('Rp 1.250.000');
        $resPo->assertSee('Urgent');

        // PO Proyek views the show page from project balance requests route
        $resProyek = $this->actingAs($this->poProyekUser)
            ->get(route('project-balance-requests.show', $request));

        $resProyek->assertOk();
        $resProyek->assertSee('PKL-202611-0099');
        $resProyek->assertSee('Semen Darurat');
    }

    public function test_po_pusat_can_approve_balance_request_via_create_form_with_payment_details()
    {
        Storage::fake('public');

        $request = ProjectBalanceRequest::create([
            'request_number' => 'PKL-202611-0006',
            'request_date'   => now()->toDateString(),
            'project_id'     => $this->project->id,
            'requested_by'   => $this->poProyekUser->id,
            'amount'         => 2500000.00,
            'purpose'        => 'Operasional alat berat lapangan',
            'urgency'        => 'urgent',
            'status'         => 'pending',
        ]);

        $initialBalance = (float) $this->project->operational_balance;

        $file = UploadedFile::fake()->create('bukti_transfer.jpg', 500, 'image/jpeg');

        $response = $this->actingAs($this->poPusatUser)
            ->post(route('payments.approveBalanceRequest', $request), [
                'payment_date'     => now()->toDateString(),
                'payment_method'   => 'bank_transfer',
                'bank_account'     => 'BCA - 89201928 PT Arsikon',
                'reference_number' => 'TRF-BCA-202611-001',
                'proof_file'       => $file,
                'notes'            => 'Pencairan disetujui via transfer BCA',
            ]);

        $response->assertRedirect(route('payments.index'));

        $request->refresh();
        $this->assertEquals('approved', $request->status);
        $this->assertEquals($this->poPusatUser->id, $request->approved_by);
        $this->assertEquals('TRF-BCA-202611-001', $request->reference_number);

        $this->project->refresh();
        $this->assertEquals($initialBalance + 2500000.00, (float) $this->project->operational_balance);

        $this->assertDatabaseHas('payments', [
            'project_id'       => $this->project->id,
            'amount'           => 2500000.00,
            'payment_type'     => 'disbursement_kas',
            'reference_number' => 'TRF-BCA-202611-001',
        ]);
    }
}
