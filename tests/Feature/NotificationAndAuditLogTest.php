<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use App\Notifications\SystemNotification;
use App\Services\AuditLogService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotificationAndAuditLogTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $this->adminUser = User::where('email', 'admin.pusat@arsikon.co.id')->firstOrFail();
    }

    public function test_can_send_system_notification_to_user(): void
    {
        Notification::fake();

        $this->adminUser->notify(new SystemNotification(
            'Pengajuan Baru',
            'Gudang Proyek A mengajukan permintaan material',
            'warning'
        ));

        Notification::assertSentTo(
            $this->adminUser,
            SystemNotification::class,
            function ($notification) {
                return $notification->title === 'Pengajuan Baru';
            }
        );
    }

    public function test_can_record_audit_log_event(): void
    {
        AuditLogService::log(
            'APPROVE',
            $this->adminUser,
            ['status' => 'submitted'],
            ['status' => 'approved'],
            $this->adminUser
        );

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->adminUser->id,
            'event' => 'APPROVE',
            'auditable_type' => User::class,
            'auditable_id' => $this->adminUser->id,
        ]);
    }

    public function test_can_view_notifications_and_mark_as_read(): void
    {
        $this->adminUser->notifications()->delete();

        $this->adminUser->notify(new SystemNotification(
            'Test Notif',
            'Ini pesan notifikasi test',
            'info'
        ));

        $this->assertEquals(1, $this->adminUser->unreadNotifications()->count());

        $response = $this->actingAs($this->adminUser)->get(route('notifications.index'));
        $response->assertStatus(200);

        $notif = $this->adminUser->unreadNotifications()->first();

        // Mark single as read
        $markResponse = $this->actingAs($this->adminUser)->post(route('notifications.markAsRead', $notif->id));
        $markResponse->assertRedirect();
        $this->assertEquals(0, $this->adminUser->unreadNotifications()->count());

        // Test mark all as read
        $this->adminUser->notify(new SystemNotification('Test 2', 'Pesan 2', 'warning'));
        $this->assertEquals(1, $this->adminUser->unreadNotifications()->count());

        $markAllResponse = $this->actingAs($this->adminUser)->post(route('notifications.markAllAsRead'));
        $markAllResponse->assertRedirect();
        $this->assertEquals(0, $this->adminUser->unreadNotifications()->count());
    }

    public function test_karyawan_loan_request_only_notifies_project_warehouse_admin_not_central_admin(): void
    {
        Notification::fake();

        $projectWarehouse = \App\Models\Warehouse::where('is_central', false)->firstOrFail();
        $adminProyek = User::where('email', 'admin.proyek1@arsikon.co.id')->firstOrFail();
        $adminPusat = User::where('email', 'admin.pusat@arsikon.co.id')->firstOrFail();
        $karyawan = User::where('email', 'karyawan@arsikon.co.id')->firstOrFail();

        \App\Services\NotificationHelper::notifyApprovers(
            'Pengajuan Peminjaman Alat: #LOAN-001',
            'Pekerja Lapangan mengajukan peminjaman alat',
            'approval_needed',
            null,
            $projectWarehouse->id
        );

        // Project Admin must receive
        Notification::assertSentTo($adminProyek, SystemNotification::class);

        // Central Admin and Karyawan must NOT receive
        Notification::assertNotSentTo($adminPusat, SystemNotification::class);
        Notification::assertNotSentTo($karyawan, SystemNotification::class);
    }

    public function test_material_request_to_central_notifies_central_admin_not_karyawan(): void
    {
        Notification::fake();

        $adminPusat = User::where('email', 'admin.pusat@arsikon.co.id')->firstOrFail();
        $karyawan = User::where('email', 'karyawan@arsikon.co.id')->firstOrFail();
        $adminProyek = User::where('email', 'admin.proyek1@arsikon.co.id')->firstOrFail();

        \App\Services\NotificationHelper::notifyCentralWarehouseAdmins(
            'Permintaan Material: #MR-001',
            'Permintaan material diajukan oleh Admin Proyek',
            'approval_needed'
        );

        // Central Admin must receive
        Notification::assertSentTo($adminPusat, SystemNotification::class);

        // Karyawan and Project Admin must NOT receive
        Notification::assertNotSentTo($karyawan, SystemNotification::class);
        Notification::assertNotSentTo($adminProyek, SystemNotification::class);
    }

    public function test_unread_count_endpoint_returns_latest_notification_data_and_count(): void
    {
        $user = User::factory()->create();
        $user->notify(new SystemNotification(
            'Permintaan Baru Masuk',
            'Ada permintaan material baru yang butuh verifikasi.',
            'approval_needed',
            route('notifications.index'),
            'approval'
        ));

        $response = $this->actingAs($user)->getJson(route('notifications.unreadCount'));

        $response->assertStatus(200);
        $response->assertJson([
            'count' => 1,
            'latest' => [
                'title' => 'Permintaan Baru Masuk',
                'message' => 'Ada permintaan material baru yang butuh verifikasi.',
                'type' => 'approval_needed',
                'sound_type' => 'approval',
            ],
        ]);
    }
}

