<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Define Permissions
        $permissions = [
            'users.manage', 'view users', 'create users', 'edit users', 'delete users',
            'projects.manage',
            'warehouses.manage',
            'suppliers.manage', 'view suppliers', 'create suppliers', 'edit suppliers', 'delete suppliers',
            'materials.manage', 'view materials', 'create materials', 'edit materials', 'delete materials',
            'tools.manage', 'view tools', 'create tools', 'edit tools', 'delete tools',
            'goods_receipts.create', 'view goods receipts', 'create goods receipts',
            'material_requests.create', 'view material requests', 'create material requests', 'material_requests.approve',
            'distributions.create', 'view distributions', 'create distributions', 'distributions.receive',
            'tools.assign', 'view tool assignments', 'create tool assignments', 'return tool assignments', 'tools.inspect_return',
            'stock_opname.create', 'view stock opname', 'create stock opname', 'stock_opname.approve', 'approve stock opname',
            'reports.view_all', 'view reports',
            'audit_logs.view', 'view audit logs', 'view inventory',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // 2. Create Roles & Assign Permissions
        $ownerRole = Role::firstOrCreate(['name' => 'Owner']);
        $ownerRole->syncPermissions(Permission::all());

        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $adminRole->syncPermissions([
            'projects.manage',
            'warehouses.manage',
            'suppliers.manage',
            'materials.manage',
            'tools.manage',
            'goods_receipts.create',
            'material_requests.approve',
            'distributions.create',
            'tools.assign',
            'tools.inspect_return',
            'stock_opname.create',
            'stock_opname.approve',
            'reports.view_all',
            'audit_logs.view',
        ]);

        $userRole = Role::firstOrCreate(['name' => 'User']);
        $userRole->syncPermissions([
            'view material requests',
            'create material requests',
            'material_requests.create',
            'view distributions',
            'distributions.receive',
            'view tool assignments',
            'create tool assignments',
            'tools.assign',
            'view stock opname',
            'create stock opname',
            'stock_opname.create',
            'view inventory',
        ]);

        // 3. Create Default Central Warehouse & Sample Project
        $centralWarehouse = Warehouse::firstOrCreate(
            ['code' => 'W-CENTRAL'],
            [
                'name' => 'Gudang Pusat PT Arsikon',
                'type' => 'central',
                'is_central' => true,
                'address' => 'Jl. Industri Utama No. 1, Jakarta',
            ]
        );

        $sampleProject = Project::firstOrCreate(
            ['code' => 'PRJ-001'],
            [
                'name' => 'Proyek Pembangunan Gedung A',
                'location' => 'Jakarta Selatan',
                'status' => 'active',
                'start_date' => now()->toDateString(),
            ]
        );

        $projectWarehouse = Warehouse::firstOrCreate(
            ['code' => 'W-PRJ-001'],
            [
                'project_id' => $sampleProject->id,
                'name' => 'Gudang Proyek Gedung A',
                'type' => 'project',
                'is_central' => false,
                'address' => 'Site Office Gedung A',
            ]
        );

        // 4. Create Initial System Users
        $ownerUser = User::firstOrCreate(
            ['email' => 'owner@arsikon.co.id'],
            [
                'name' => 'Bapak Owner',
                'password' => Hash::make('password123'),
            ]
        );
        $ownerUser->assignRole($ownerRole);
        $ownerUser->warehouses()->syncWithoutDetaching([$centralWarehouse->id, $projectWarehouse->id]);

        $adminUser = User::firstOrCreate(
            ['email' => 'admin.pusat@arsikon.co.id'],
            [
                'name' => 'Admin Gudang Pusat',
                'password' => Hash::make('password123'),
            ]
        );
        $adminUser->assignRole($adminRole);
        $adminUser->warehouses()->syncWithoutDetaching([$centralWarehouse->id]);

        $projectUser = User::firstOrCreate(
            ['email' => 'user.proyek@arsikon.co.id'],
            [
                'name' => 'Petugas Gudang Proyek A',
                'password' => Hash::make('password123'),
            ]
        );
        $projectUser->assignRole($userRole);
        $projectUser->warehouses()->syncWithoutDetaching([$projectWarehouse->id]);
    }
}
