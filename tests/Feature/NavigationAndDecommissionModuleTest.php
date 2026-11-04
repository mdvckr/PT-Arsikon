<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavigationAndDecommissionModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
    }

    public function test_sidebar_renders_surat_jalan_under_logistik_and_removes_retur_and_stock_opname(): void
    {
        $gudangProyekUser = User::factory()->create(['email_verified_at' => now()]);
        $gudangProyekUser->assignRole('Admin Gudang Proyek');

        $response = $this->actingAs($gudangProyekUser)->get(route('dashboard'));

        $response->assertOk();
        // Surat Jalan harus tampil untuk user logistik gudang proyek
        $response->assertSee(route('distributions.index'));
        $response->assertSee('Surat Jalan');

        // Retur Material dan Stock Opname tidak boleh tampil di sidebar
        $response->assertDontSee('Retur Material');
        $response->assertDontSee('Stock Opname');
    }

    public function test_returns_and_stock_opname_routes_are_decommissioned_or_forbidden(): void
    {
        $gudangProyekUser = User::factory()->create(['email_verified_at' => now()]);
        $gudangProyekUser->assignRole('Admin Gudang Proyek');

        $resReturn = $this->actingAs($gudangProyekUser)->get('/returns');
        $this->assertTrue(in_array($resReturn->status(), [403, 404]), 'Route returns harus berstatus 403 atau 404');

        $resStockOpname = $this->actingAs($gudangProyekUser)->get('/stock-opnames');
        $this->assertTrue(in_array($resStockOpname->status(), [403, 404]), 'Route stock-opnames harus berstatus 403 atau 404');
    }
}

