<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProcurementRolePermissionSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_procurement_permissions_command_executes_successfully(): void
    {
        $exitCode = Artisan::call('permissions:sync-procurement');
        $this->assertSame(0, $exitCode);

        // Verify that canonical roles are created
        $this->assertTrue(Role::where('name', 'Owner')->exists());
        $this->assertTrue(Role::where('name', 'Admin Pusat')->exists());
        $this->assertTrue(Role::where('name', 'Admin Pengadaan')->exists());
        $this->assertTrue(Role::where('name', 'Admin Logistik Pusat')->exists());
        $this->assertTrue(Role::where('name', 'Admin Logistik Proyek')->exists());
        $this->assertTrue(Role::where('name', 'Site Engineer')->exists());
    }

    public function test_owner_role_is_strictly_view_only(): void
    {
        Artisan::call('permissions:sync-procurement');

        $owner = Role::findByName('Owner', 'web');
        $this->assertNotEmpty($owner->permissions);

        foreach ($owner->permissions as $permission) {
            $this->assertStringStartsWith('view ', $permission->name);
        }

        $this->assertFalse($owner->hasPermissionTo('create purchase orders'));
        $this->assertFalse($owner->hasPermissionTo('confirm goods receipts'));
    }

    public function test_admin_pusat_has_all_permissions(): void
    {
        Artisan::call('permissions:sync-procurement');

        $adminPusat = Role::findByName('Admin Pusat', 'web');
        $totalPermissions = Permission::count();

        $this->assertSame($totalPermissions, $adminPusat->permissions()->count());
        $this->assertTrue($adminPusat->hasPermissionTo('approve purchase orders'));
        $this->assertTrue($adminPusat->hasPermissionTo('close purchase orders'));
        $this->assertTrue($adminPusat->hasPermissionTo('verify payments'));
        $this->assertTrue($adminPusat->hasPermissionTo('approve procurement'));
    }

    public function test_admin_po_role_permissions_comply_with_t16(): void
    {
        Artisan::call('permissions:sync-procurement');

        $adminPO = Role::findByName('Admin Pengadaan', 'web');

        // Boleh:
        $this->assertTrue($adminPO->hasPermissionTo('create purchase orders'));
        $this->assertTrue($adminPO->hasPermissionTo('view purchase orders'));
        $this->assertTrue($adminPO->hasPermissionTo('send purchase orders'));
        $this->assertTrue($adminPO->hasPermissionTo('cancel purchase orders'));
        $this->assertTrue($adminPO->hasPermissionTo('create procurement'));
        $this->assertTrue($adminPO->hasPermissionTo('view procurement'));
        $this->assertTrue($adminPO->hasPermissionTo('create payments'));
        $this->assertTrue($adminPO->hasPermissionTo('view payments'));
        $this->assertTrue($adminPO->hasPermissionTo('approve material requests'));

        // Dilarang keras (Hanya Admin Pusat / Admin):
        $this->assertFalse($adminPO->hasPermissionTo('view distributions'));
        $this->assertFalse($adminPO->hasPermissionTo('create distributions'));
        $this->assertFalse($adminPO->hasPermissionTo('create transit losses'));
        $this->assertFalse($adminPO->hasPermissionTo('approve purchase orders'));
        $this->assertFalse($adminPO->hasPermissionTo('close purchase orders'));
        $this->assertFalse($adminPO->hasPermissionTo('verify payments'));
        $this->assertFalse($adminPO->hasPermissionTo('approve procurement'));
        $this->assertFalse($adminPO->hasPermissionTo('approve transit losses'));
    }

    public function test_site_engineer_role_permissions_comply_with_t16(): void
    {
        Artisan::call('permissions:sync-procurement');

        $siteEngineer = Role::findByName('Site Engineer', 'web');

        // Boleh:
        $this->assertTrue($siteEngineer->hasPermissionTo('create material requests'));
        $this->assertTrue($siteEngineer->hasPermissionTo('view material requests'));

        // Dilarang: Surat jalan hanya dikeluarkan gudang/logistik
        $this->assertFalse($siteEngineer->hasPermissionTo('create distributions'));
    }

    public function test_command_is_idempotent(): void
    {
        Artisan::call('permissions:sync-procurement');
        $firstCount = Permission::count();

        Artisan::call('permissions:sync-procurement');
        $secondCount = Permission::count();

        $this->assertSame($firstCount, $secondCount);

        $adminPO = Role::findByName('Admin Pengadaan', 'web');
        $this->assertFalse($adminPO->hasPermissionTo('approve purchase orders'));
        $this->assertFalse($adminPO->hasPermissionTo('verify payments'));
        $this->assertTrue($adminPO->hasPermissionTo('approve material requests'));
    }
}
