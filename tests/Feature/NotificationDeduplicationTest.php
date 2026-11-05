<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationDeduplicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_does_not_render_legacy_glass_notification_stack(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('Owner');

        $response = $this->actingAs($user)
            ->withSession(['success' => 'Operasi berhasil disimpan'])
            ->get(route('dashboard'));

        $response->assertOk();
        $response->assertDontSee('id="glass-notification-stack"', false);
        $response->assertDontSee('<x-notification.glass-stack', false);
        $response->assertDontSee("title: 'Berhasil'", false);
        $response->assertSee('window.serverFlashNotifications', false);
        $response->assertSee('Operasi berhasil disimpan');
    }

    public function test_notification_center_script_delegates_to_window_notify_without_duplicate_banners(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('Owner');

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('sweetalert-notifications.js');
        $response->assertSee('showNotificationToast');
    }
}
