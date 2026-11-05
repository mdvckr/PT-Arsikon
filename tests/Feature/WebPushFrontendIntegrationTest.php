<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebPushFrontendIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_renders_webpush_client_assets_and_vapid_meta(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('Owner');

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('vapid-public-key');
        $response->assertSee('webpush-client.js');
    }
}

