<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Sprint 2 - Issue 9: Karyawan Role Cleanup
 * Memastikan Karyawan tidak memiliki akses ke MR dan Distributions.
 */
class KaryawanRoleTest extends TestCase
{
    use RefreshDatabase;

    protected User $karyawan;
    protected User $adminProyekA;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $this->karyawan     = User::where('email', 'karyawan@arsikon.co.id')->firstOrFail();
        $this->adminProyekA = User::where('email', 'admin.proyek1@arsikon.co.id')->firstOrFail();
    }

    // Karyawan TIDAK boleh akses Material Requests

    public function test_karyawan_cannot_view_material_requests_index(): void
    {
        $this->actingAs($this->karyawan)
            ->get('/material-requests')
            ->assertForbidden();
    }

    public function test_karyawan_cannot_access_material_request_create(): void
    {
        $this->actingAs($this->karyawan)
            ->get('/material-requests/create')
            ->assertForbidden();
    }

    // Karyawan TIDAK boleh akses Distributions

    public function test_karyawan_cannot_view_distributions_index(): void
    {
        $this->actingAs($this->karyawan)
            ->get('/distributions')
            ->assertForbidden();
    }

    public function test_karyawan_cannot_access_distribution_create(): void
    {
        $this->actingAs($this->karyawan)
            ->get('/distributions/create')
            ->assertForbidden();
    }

    // Karyawan BOLEH akses Pemakaian Material

    public function test_karyawan_can_view_material_usages_index(): void
    {
        $this->actingAs($this->karyawan)
            ->get('/material-usages')
            ->assertOk();
    }

    public function test_karyawan_can_access_material_usage_create(): void
    {
        $this->actingAs($this->karyawan)
            ->get('/material-usages/create')
            ->assertOk();
    }

    // Karyawan BOLEH akses Tool Assignments

    public function test_karyawan_can_view_tool_assignments_index(): void
    {
        $this->actingAs($this->karyawan)
            ->get('/tool-assignments')
            ->assertOk();
    }

    // Karyawan BOLEH akses Inventory

    public function test_karyawan_can_view_inventory(): void
    {
        $this->actingAs($this->karyawan)
            ->get('/inventory')
            ->assertOk();
    }

    // Permission check menggunakan model

    public function test_karyawan_does_not_have_view_material_requests_permission(): void
    {
        $this->assertFalse($this->karyawan->hasPermissionTo('view material requests'));
    }

    public function test_karyawan_does_not_have_view_distributions_permission(): void
    {
        $this->assertFalse($this->karyawan->hasPermissionTo('view distributions'));
    }

    public function test_karyawan_has_create_material_usages_permission(): void
    {
        $this->assertTrue($this->karyawan->hasPermissionTo('create material usages'));
    }

    public function test_karyawan_has_create_tool_assignments_permission(): void
    {
        $this->assertTrue($this->karyawan->hasPermissionTo('create tool assignments'));
    }

    // Admin Proyek masih bisa akses MR dan Distributions

    public function test_admin_proyek_can_still_view_material_requests(): void
    {
        $this->actingAs($this->adminProyekA)
            ->get('/material-requests')
            ->assertOk();
    }

    public function test_admin_proyek_can_still_view_distributions(): void
    {
        $this->actingAs($this->adminProyekA)
            ->get('/distributions')
            ->assertOk();
    }
}
