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
}
