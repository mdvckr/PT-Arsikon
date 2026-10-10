<?php

namespace Tests\Feature;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleManagementTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_only_canonical_roles_exist_in_database(): void
    {
        $roleNames = Role::pluck('name')->toArray();

        // Verifikasi tidak ada alias duplikat lama
        $this->assertNotContains('Admin', $roleNames);
        $this->assertNotContains('User', $roleNames);

        // Verifikasi role kanonikal ada
        $this->assertContains('Owner', $roleNames);
        $this->assertContains('Admin Pusat', $roleNames);
        $this->assertContains('Admin Gudang Pusat', $roleNames);
        $this->assertContains('Admin Gudang Proyek', $roleNames);
        $this->assertContains('Admin PO', $roleNames);
        $this->assertContains('Karyawan', $roleNames);
    }

    public function test_protected_roles_cannot_be_deleted(): void
    {
        $adminPusat = User::where('email', 'admin.pusat@arsikon.co.id')->first();
        if (!$adminPusat) {
            $this->markTestSkipped('Admin Pusat user not found');
        }

        $role = Role::where('name', 'Admin Pusat')->first();

        $response = $this->actingAs($adminPusat)->delete("/roles/{$role->id}");

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('roles', ['name' => 'Admin Pusat']);
    }

    public function test_reserved_role_names_are_rejected(): void
    {
        $adminPusat = User::where('email', 'admin.pusat@arsikon.co.id')->first();
        if (!$adminPusat) {
            $this->markTestSkipped('Admin Pusat user not found');
        }

        $response = $this->actingAs($adminPusat)->post('/roles', [
            'name' => 'admin',
        ]);

        $response->assertSessionHasErrors('name');
    }
}
