<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\SupabaseSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupabaseSyncTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $karyawanUser;

    protected function setUp(): void
    {
        parent::setUp();
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $this->adminUser = User::where('email', 'admin.pusat@arsikon.co.id')->firstOrFail();
        $this->karyawanUser = User::where('email', 'user.proyek@arsikon.co.id')->firstOrFail();
    }

    public function test_guest_cannot_access_supabase_sync_page(): void
    {
        $response = $this->get(route('supabase-sync.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_unauthorized_role_cannot_access_supabase_sync_page(): void
    {
        $response = $this->actingAs($this->karyawanUser)->get(route('supabase-sync.index'));
        $response->assertStatus(403);
    }

    public function test_admin_can_access_supabase_sync_page(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('supabase-sync.index'));
        $response->assertOk();
        $response->assertSee('Backup & Sinkronisasi Supabase Cloud', false);
        $response->assertSee('Cadangkan ke Supabase (PUSH)', false);
        $response->assertSee('Tarik ke Database Lokal (PULL / RESTORE)', false);
    }

    public function test_test_connection_ajax_endpoint_returns_json(): void
    {
        $response = $this->actingAs($this->adminUser)->postJson(route('supabase-sync.test'));
        $response->assertOk();
        $response->assertJsonStructure(['connected', 'message']);
    }

    public function test_service_returns_graceful_response_when_unconfigured(): void
    {
        config(['database.connections.supabase.host' => null]);
        $service = app(SupabaseSyncService::class);
        $result = $service->testConnection();

        $this->assertFalse($result['connected']);
        $this->assertStringContainsString('belum dikonfigurasi', $result['message']);
    }

    public function test_artisan_command_test_flag_exits_cleanly(): void
    {
        $this->artisan('supabase:sync', ['--test' => true])
            ->expectsOutputToContain('PT-Arsikon — Supabase Cloud Catalog Sync')
            ->assertExitCode(0);
    }
}
