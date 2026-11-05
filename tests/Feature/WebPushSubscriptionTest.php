<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebPushSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_store_and_delete_push_subscription(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $user = User::factory()->create();

        $payload = [
            'endpoint'    => 'https://updates.push.services.mozilla.com/wpush/v2/test-endpoint-token',
            'keys'        => [
                'auth'   => 'test-auth-secret-12345',
                'p256dh' => 'test-p256dh-public-key-abcdef',
            ],
            'contentEncoding' => 'aesgcm',
        ];

        $response = $this->actingAs($user)->postJson(route('push-subscriptions.store'), $payload);
        $response->assertOk();
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('push_subscriptions', [
            'subscribable_id'   => $user->id,
            'subscribable_type' => User::class,
            'endpoint'          => 'https://updates.push.services.mozilla.com/wpush/v2/test-endpoint-token',
        ]);

        $deleteResponse = $this->actingAs($user)->deleteJson(route('push-subscriptions.destroy'), [
            'endpoint' => 'https://updates.push.services.mozilla.com/wpush/v2/test-endpoint-token',
        ]);
        $deleteResponse->assertOk();
        $deleteResponse->assertJson(['success' => true]);

        $this->assertDatabaseMissing('push_subscriptions', [
            'subscribable_id' => $user->id,
            'endpoint'        => 'https://updates.push.services.mozilla.com/wpush/v2/test-endpoint-token',
        ]);
    }
}

