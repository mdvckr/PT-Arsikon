<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AdminPoNotaRbacTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles & permissions needed for testing
        $permissions = [
            'view material requests',
            'create material requests',
            'view purchase receipts',
            'create purchase receipts',
            'delete purchase receipts',
            'view materials',
            'view suppliers',
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        $role = Role::firstOrCreate(['name' => 'admin-po-nota', 'guard_name' => 'web']);
        $role->syncPermissions([
            'view material requests',
            'view purchase receipts',
            'create purchase receipts',
            'delete purchase receipts',
            'view materials',
            'view suppliers',
        ]);
    }

    public function test_admin_po_nota_can_view_po_lapangan_list(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin-po-nota');

        $response = $this->actingAs($user)->get(route('material-requests.index'));
        $response->assertOk();
    }

    public function test_admin_po_nota_cannot_access_create_po_lapangan(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin-po-nota');

        $response = $this->actingAs($user)->get(route('material-requests.create'));
        $response->assertForbidden();
    }

    public function test_admin_po_nota_cannot_store_po_lapangan(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin-po-nota');

        $response = $this->actingAs($user)->post(route('material-requests.store'), [
            'needed_at' => now()->addDays(2)->toDateString(),
        ]);
        $response->assertForbidden();
    }

    public function test_admin_po_nota_can_view_and_create_nota_pembelian_harian(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin-po-nota');

        $indexResponse = $this->actingAs($user)->get(route('purchase-receipts.index'));
        $indexResponse->assertOk();

        $createResponse = $this->actingAs($user)->get(route('purchase-receipts.create'));
        $createResponse->assertOk();
    }
}
