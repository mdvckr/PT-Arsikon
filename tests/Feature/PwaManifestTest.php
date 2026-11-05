<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PwaManifestTest extends TestCase
{
    use RefreshDatabase;

    public function test_pwa_manifest_and_service_worker_are_accessible_and_linked(): void
    {
        $manifestResponse = $this->get('/manifest.json');
        $manifestResponse->assertOk();
        $manifestResponse->assertHeader('Content-Type', 'application/json');
        $this->assertStringContainsString('PT Arsikon', $manifestResponse->getContent());
        $this->assertStringContainsString('standalone', $manifestResponse->getContent());

        $swResponse = $this->get('/sw.js');
        $swResponse->assertOk();

        $this->seed(RoleAndPermissionSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('Owner');

        $viewResponse = $this->actingAs($user)->get(route('dashboard'));
        $viewResponse->assertOk();
        $viewResponse->assertSee('<link rel="manifest" href="/manifest.json">', false);
        $viewResponse->assertSee('serviceWorker.register', false);
    }
}

