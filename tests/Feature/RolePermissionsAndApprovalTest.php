<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Warehouse;
use App\Models\Material;
use App\Models\MaterialRequest;
use App\Services\NotificationHelper;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RolePermissionsAndApprovalTest extends TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    protected User $adminPusat;
    protected User $owner;
    protected User $karyawan;
    protected User $dualRoleAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RoleAndPermissionSeeder::class);

        // 1. Setup user Admin Pusat
        $this->adminPusat = User::firstOrCreate(
            ['email' => 'admin.test@arsikon.co.id'],
            ['name' => 'Admin Pusat Test', 'password' => bcrypt('password')]
        );
        $this->adminPusat->syncRoles(['Admin Pusat']);

        // 2. Setup user Owner
        $this->owner = User::firstOrCreate(
            ['email' => 'owner.test@arsikon.co.id'],
            ['name' => 'Owner Test', 'password' => bcrypt('password')]
        );
        $this->owner->syncRoles(['Owner']);

        // 3. Setup user Karyawan
        $this->karyawan = User::firstOrCreate(
            ['email' => 'karyawan.test@arsikon.co.id'],
            ['name' => 'Karyawan Test', 'password' => bcrypt('password')]
        );
        $this->karyawan->syncRoles(['Karyawan']);

        // 4. Setup user Dual-Role (Admin Gudang Proyek + Karyawan)
        $this->dualRoleAdmin = User::firstOrCreate(
            ['email' => 'dual.role@arsikon.co.id'],
            ['name' => 'Dual Role Admin Test', 'password' => bcrypt('password')]
        );
        $this->dualRoleAdmin->syncRoles(['Admin Gudang Proyek', 'Karyawan']);
    }

    public function test_admin_pusat_has_full_access_to_all_abilities(): void
    {
        $this->actingAs($this->adminPusat);

        $this->assertTrue(Gate::allows('view reports'));
        $this->assertTrue(Gate::allows('view materials'));
        $this->assertTrue(Gate::allows('create materials'));
        $this->assertTrue(Gate::allows('delete materials'));
        $this->assertTrue(Gate::allows('approve material requests'));
        $this->assertTrue(Gate::allows('approve tool assignments'));
        $this->assertTrue(Gate::allows('approve procurement'));
        $this->assertTrue(Gate::allows('verify payments'));
        $this->assertTrue(Gate::allows('projects.manage'));
    }

    public function test_owner_is_strictly_read_only_and_cannot_mutate_or_approve(): void
    {
        $this->actingAs($this->owner);

        // Owner BISA melihat (view / read-only)
        $this->assertTrue(Gate::allows('view reports'));
        $this->assertTrue(Gate::allows('view audit logs'));
        $this->assertTrue(Gate::allows('view materials'));
        $this->assertTrue(Gate::allows('view material requests'));
        $this->assertTrue(Gate::allows('view procurement'));
        $this->assertTrue(Gate::allows('view payments'));

        // Owner DITOLAK secara mutlak untuk aksi mutasi & approval
        $this->assertFalse(Gate::allows('create materials'));
        $this->assertFalse(Gate::allows('edit materials'));
        $this->assertFalse(Gate::allows('delete materials'));
        $this->assertFalse(Gate::allows('approve material requests'));
        $this->assertFalse(Gate::allows('approve tool assignments'));
        $this->assertFalse(Gate::allows('approve procurement'));
        $this->assertFalse(Gate::allows('verify payments'));
        $this->assertFalse(Gate::allows('projects.manage'));
    }

    public function test_karyawan_can_request_materials_and_tools_but_cannot_approve(): void
    {
        $this->actingAs($this->karyawan);

        // Karyawan diizinkan membuat permintaan / pinjam alat
        $this->assertTrue(Gate::allows('create material requests'));
        $this->assertTrue(Gate::allows('create tool assignments'));

        // Karyawan tidak diizinkan approve atau mutasi master
        $this->assertFalse(Gate::allows('approve material requests'));
        $this->assertFalse(Gate::allows('approve tool assignments'));
        $this->assertFalse(Gate::allows('delete inventory'));
    }

    public function test_dual_role_admin_gudang_with_karyawan_does_not_conflict(): void
    {
        $this->actingAs($this->dualRoleAdmin);

        // Memiliki izin operasional gudang dan pengajuan
        $this->assertTrue(Gate::allows('create material requests'));
        $this->assertTrue(Gate::allows('create tool assignments'));
        $this->assertTrue(Gate::allows('view goods receipts'));
        $this->assertTrue(Gate::allows('create goods receipts'));

        // Ship distributions diizinkan untuk Admin Gudang meskipun merangkap Karyawan
        $this->assertTrue(Gate::allows('ship distributions'));
    }

    public function test_notification_helper_dispatches_approvals_to_admin_pusat_and_monitoring_to_owner(): void
    {
        Notification::fake();

        NotificationHelper::notifyApprovers(
            "Permintaan Material Baru",
            "Diajukan oleh Karyawan Lapangan",
            "approval_needed",
            "/material-requests/1"
        );

        // Verifikasi Admin Pusat menerima notifikasi bertipe 'approval_needed'
        Notification::assertSentTo(
            $this->adminPusat,
            \App\Notifications\SystemNotification::class,
            function ($notification) {
                return $notification->type === 'approval_needed';
            }
        );

        // Verifikasi Owner menerima notifikasi bertipe 'info' (pemantauan read-only)
        Notification::assertSentTo(
            $this->owner,
            \App\Notifications\SystemNotification::class,
            function ($notification) {
                return $notification->type === 'info';
            }
        );
    }

    public function test_owner_http_mutations_are_blocked_with_403_forbidden(): void
    {
        // Owner mencoba melakukan HTTP POST untuk membuat material baru
        $response = $this->actingAs($this->owner)->post(route('materials.store'), [
            'name' => 'Semen Padang Test',
            'code' => 'MAT-TEST-001',
        ]);

        $response->assertStatus(403);

        // Owner mencoba melakukan approve procurement
        $responseApprove = $this->actingAs($this->owner)->post('/procurement/999/approve');
        $responseApprove->assertStatus(403);
    }

    public function test_request_submitted_event_broadcasts_to_reverb_channels(): void
    {
        Notification::fake();
        \Illuminate\Support\Facades\Event::fake([
            \App\Events\RequestSubmitted::class,
        ]);

        NotificationHelper::notifyApprovers(
            "Pengajuan Pengadaan Baru",
            "Diajukan oleh staf gudang",
            "approval_needed",
            "/procurement/10",
            1
        );

        \Illuminate\Support\Facades\Event::assertDispatched(\App\Events\RequestSubmitted::class, function ($event) {
            $channels = array_map(fn($c) => $c->name, $event->broadcastOn());
            return in_array('private-admin-pusat', $channels)
                && in_array('private-owner-monitoring', $channels)
                && in_array('private-warehouse.1', $channels)
                && $event->broadcastAs() === 'RequestSubmitted';
        });
    }
}

