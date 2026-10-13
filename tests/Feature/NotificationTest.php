<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\SystemNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user1;
    protected User $user2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $this->user1 = User::where('email', 'admin.pusat@arsikon.co.id')->firstOrFail();
        $this->user2 = User::where('email', 'owner@arsikon.co.id')->firstOrFail();
    }

    public function test_can_fetch_unread_notifications_json(): void
    {
        $this->user1->notifications()->delete();

        $this->user1->notify(new SystemNotification(
            'Permintaan Persetujuan',
            'PO Lapangan butuh persetujuan segera',
            'approval_needed',
            '/material-requests/1'
        ));

        $this->user1->notify(new SystemNotification(
            'Penerimaan Selesai',
            'Barang telah diterima di Gudang Proyek',
            'success',
            '/goods-receipts/1'
        ));

        $response = $this->actingAs($this->user1)
            ->getJson(route('notifications.unread'));

        $response->assertOk()
            ->assertJsonStructure([
                'count',
                'unread' => [
                    '*' => [
                        'id',
                        'title',
                        'message',
                        'type',
                        'raw_type',
                        'url',
                        'created_at_human',
                    ]
                ]
            ]);

        $this->assertEquals(2, $response->json('count'));
        // Verify tint mapping
        $types = collect($response->json('unread'))->pluck('type')->all();
        $this->assertContains('success', $types);
        $this->assertContains('warning', $types);
    }

    public function test_can_mark_notification_as_read_via_json(): void
    {
        $this->user1->notifications()->delete();

        $this->user1->notify(new SystemNotification(
            'Test Notif',
            'Pesan uji coba',
            'info'
        ));

        $notif = $this->user1->unreadNotifications()->firstOrFail();

        $response = $this->actingAs($this->user1)
            ->postJson("/notifications/{$notif->id}/read");

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'id' => $notif->id
            ]);

        $this->assertEquals(0, $this->user1->fresh()->unreadNotifications()->count());
    }

    public function test_user_cannot_mark_other_users_notification(): void
    {
        $this->user2->notifications()->delete();

        $this->user2->notify(new SystemNotification(
            'Notif Rahasia Owner',
            'Laporan keuangan',
            'info'
        ));

        $notif = $this->user2->unreadNotifications()->firstOrFail();

        // User 1 tries to mark User 2's notification as read
        $response = $this->actingAs($this->user1)
            ->postJson("/notifications/{$notif->id}/read");

        $response->assertStatus(404);
        $this->assertEquals(1, $this->user2->fresh()->unreadNotifications()->count());
    }
}
