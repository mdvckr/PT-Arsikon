<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationSoundConfigTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_contains_audio_chime_synthesizer_and_gesture_unlock(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('Owner');

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('playNotificationChime');
        $response->assertSee('initAudioUnlock');
        $response->assertSee('AudioContext');
        $response->assertSee('createOscillator');
    }
}

