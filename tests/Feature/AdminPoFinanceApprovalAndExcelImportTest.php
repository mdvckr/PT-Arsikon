<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectBalanceLog;
use App\Models\ProjectBalanceRequest;
use App\Models\User;
use App\Models\Warehouse;
use Database\Seeders\MasterDataSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPoFinanceApprovalAndExcelImportTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminPo;
    protected User $adminPusat;
    protected User $owner;
    protected User $adminPoProyek;
    protected Project $project;
    protected Warehouse $warehouse;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(MasterDataSeeder::class);

        $this->project = Project::firstOrCreate(
            ['code' => 'PRJ-TEST-APPR'],
            ['name' => 'Proyek Approval Test', 'operational_balance' => 1000000.00]
        );

        $this->warehouse = Warehouse::firstOrCreate(
            ['code' => 'WH-TEST-APPR'],
            [
                'name'       => 'Gudang Proyek Approval Test',
                'project_id' => $this->project->id,
                'is_central' => false,
            ]
        );

        $this->adminPo = User::firstOrCreate(
            ['email' => 'admin.po.unit@arsikon.com'],
            ['name' => 'Admin PO Pusat Test', 'password' => bcrypt('password'), 'email_verified_at' => now()]
        );
        $this->adminPo->syncRoles(['Admin PO']);

        $this->adminPusat = User::firstOrCreate(
            ['email' => 'admin.pusat.unit@arsikon.com'],
            ['name' => 'Admin Pusat Test', 'password' => bcrypt('password'), 'email_verified_at' => now()]
        );
        $this->adminPusat->syncRoles(['Admin Pusat']);

        $this->owner = User::firstOrCreate(
            ['email' => 'owner.unit@arsikon.com'],
            ['name' => 'Owner Test', 'password' => bcrypt('password'), 'email_verified_at' => now()]
        );
        $this->owner->syncRoles(['Owner']);

        $this->adminPoProyek = User::firstOrCreate(
            ['email' => 'po.proyek.unit@arsikon.com'],
            ['name' => 'Admin PO Proyek Test', 'password' => bcrypt('password'), 'email_verified_at' => now()]
        );
        $this->adminPoProyek->syncRoles(['Admin PO Proyek']);
        $this->adminPoProyek->warehouses()->sync([$this->warehouse->id]);
    }

    public function test_only_admin_po_can_approve_or_reject_balance_request_while_admin_pusat_and_owner_are_forbidden(): void
    {
        $balanceRequest = ProjectBalanceRequest::create([
            'project_id'       => $this->project->id,
            'requested_by'     => $this->adminPoProyek->id,
            'request_date'     => '2026-11-03',
            'amount'           => 500000.00,
            'purpose'          => 'Uji alur persetujuan kas operasional',
            'urgency'          => 'normal',
            'signer_requester' => $this->adminPoProyek->name,
            'status'           => 'pending',
        ]);

        // 1. Admin Pusat mencoba menyetujui -> Wajib 403 Forbidden
        $resPusat = $this->actingAs($this->adminPusat)
            ->post(route('payments.approveBalanceRequest', $balanceRequest), [
                'reference_number' => 'REF-FAIL-PUSAT',
            ]);
        $resPusat->assertForbidden();
        $balanceRequest->refresh();
        $this->assertEquals('pending', $balanceRequest->status);

        // 2. Owner mencoba menyetujui -> Wajib 403 Forbidden
        $resOwner = $this->actingAs($this->owner)
            ->post(route('payments.approveBalanceRequest', $balanceRequest), [
                'reference_number' => 'REF-FAIL-OWNER',
            ]);
        $resOwner->assertForbidden();
        $balanceRequest->refresh();
        $this->assertEquals('pending', $balanceRequest->status);

        // 3. Admin Pusat mencoba menolak -> Wajib 403 Forbidden
        $resRejectPusat = $this->actingAs($this->adminPusat)
            ->post(route('payments.rejectBalanceRequest', $balanceRequest), [
                'rejection_reason' => 'Ditolak oleh admin pusat',
            ]);
        $resRejectPusat->assertForbidden();
        $balanceRequest->refresh();
        $this->assertEquals('pending', $balanceRequest->status);

        // 4. Admin PO menyetujui -> Berhasil approved & saldo bertambah
        $initialBalance = (float) $this->project->operational_balance;
        $this->flushSession();
        $resApprovePo = $this->actingAs($this->adminPo)
            ->from(route('payments.index'))
            ->post(route('payments.approveBalanceRequest', $balanceRequest), [
                'reference_number' => 'REF-APPR-PO-01',
                'notes'            => 'Disetujui penuh oleh Admin PO Pusat',
            ]);
        $resApprovePo->assertRedirect(route('payments.index'));
        $resApprovePo->assertSessionHas('success');

        $balanceRequest->refresh();
        $this->assertEquals('approved', $balanceRequest->status);
        $this->assertEquals($this->adminPo->id, $balanceRequest->approved_by);

        $this->project->refresh();
        $this->assertEquals($initialBalance + 500000.00, (float) $this->project->operational_balance);
    }

    public function test_admin_po_can_self_topup_balance_directly(): void
    {
        $initialBalance = (float) $this->project->operational_balance;

        $response = $this->actingAs($this->adminPo)
            ->post(route('payments.topupBalance'), [
                'project_id'       => $this->project->id,
                'amount'           => 750000,
                'reference_number' => 'TOPUP-PO-001',
                'notes'            => 'Top-up mandiri oleh Admin PO Pusat',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->project->refresh();
        $this->assertEquals($initialBalance + 750000.00, (float) $this->project->operational_balance);

        $this->assertDatabaseHas('project_balance_logs', [
            'project_id'       => $this->project->id,
            'user_id'          => $this->adminPo->id,
            'type'             => 'topup',
            'amount'           => 750000.00,
            'reference_number' => 'TOPUP-PO-001',
        ]);
    }
}
