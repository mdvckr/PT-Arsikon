<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class WorkspaceAndRoleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RoleAndPermissionSeeder::class);
    }

    public function test_roles_and_permissions_are_seeded_correctly(): void
    {
        $this->assertTrue(Role::where('name', 'Owner')->exists());
        $this->assertTrue(Role::where('name', 'Admin')->exists());
        $this->assertTrue(Role::where('name', 'User')->exists());

        $owner = User::where('email', 'owner@arsikon.co.id')->first();
        $admin = User::where('email', 'admin.pusat@arsikon.co.id')->first();
        $user = User::where('email', 'user.proyek@arsikon.co.id')->first();

        $this->assertNotNull($owner);
        $this->assertTrue($owner->hasRole('Owner'));

        $this->assertNotNull($admin);
        $this->assertTrue($admin->hasRole('Admin'));

        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('User'));
    }

    public function test_user_can_switch_active_workspace(): void
    {
        $user = User::where('email', 'owner@arsikon.co.id')->first();
        $centralWarehouse = Warehouse::where('code', 'W-CENTRAL')->first();
        $projectWarehouse = Warehouse::where('code', 'W-PRJ-001')->first();

        $response = $this->actingAs($user)->post('/workspace/switch', [
            'warehouse_id' => $projectWarehouse->id,
        ]);

        $response->assertSessionHas('active_warehouse_id', $projectWarehouse->id);
    }

    public function test_user_cannot_switch_to_unauthorized_workspace(): void
    {
        $projectUser = User::where('email', 'user.proyek@arsikon.co.id')->first();
        $centralWarehouse = Warehouse::where('code', 'W-CENTRAL')->first();

        $response = $this->actingAs($projectUser)->post('/workspace/switch', [
            'warehouse_id' => $centralWarehouse->id,
        ]);

        $response->assertSessionHas('error');
        $this->assertNotEquals($centralWarehouse->id, session('active_warehouse_id'));
    }
}
