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
            'goods_receipts.create', 'view goods receipts', 'create goods receipts', 'confirm goods receipts',
            'material_requests.create', 'view material requests', 'create material requests', 'material_requests.approve', 'approve material requests',
            'distributions.create', 'view distributions', 'create distributions', 'distributions.receive', 'ship distributions', 'receive distributions',
            'tools.assign', 'view tool assignments', 'create tool assignments', 'return tool assignments', 'approve tool assignments', 'tools.inspect_return',
            'stock_opname.create', 'view stock opname', 'create stock opname', 'stock_opname.approve', 'approve stock opname',
            'reports.view_all', 'view reports',
            'audit_logs.view', 'view audit logs', 'view inventory', 'delete inventory',
            // Categories
            'view categories', 'create categories', 'edit categories', 'delete categories',
            // Procurement & PO
            'view procurement', 'create procurement', 'approve procurement',
            'view purchase orders', 'create purchase orders', 'send purchase orders', 'cancel purchase orders',
            'view payments', 'create payments', 'verify payments',
            'view returns', 'create returns', 'approve returns', 'receive returns',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // 2. Create Roles & Assign Permissions
        $ownerRole = Role::firstOrCreate(['name' => 'Owner']);
        $ownerRole->syncPermissions(Permission::all());

        $adminPusatRole = Role::firstOrCreate(['name' => 'Admin Gudang Pusat']);
        $adminRole = Role::firstOrCreate(['name' => 'Admin']); // Backwards-compatible alias
        $pusatPermissions = [
            'projects.manage',
            'warehouses.manage',
            'suppliers.manage',
            'materials.manage',
            'tools.manage',
            'material_requests.approve',
            'approve material requests',
            'distributions.create',
            'view distributions',
            'create distributions',
            'ship distributions',
            'receive distributions',
            'tools.assign',
            'view tool assignments',
            'create tool assignments',
            'return tool assignments',
            'approve tool assignments',
            'tools.inspect_return',
            'stock_opname.create',
            'stock_opname.approve',
            'approve stock opname',
            'reports.view_all',
            'audit_logs.view',
            'view inventory',
            'view returns',
            'approve returns',
            'receive returns',
        ];
        $adminPusatRole->syncPermissions($pusatPermissions);
        $adminRole->syncPermissions($pusatPermissions);

        $adminProyekRole = Role::firstOrCreate(['name' => 'Admin Gudang Proyek']);
        $userRole = Role::firstOrCreate(['name' => 'User']); // Backwards-compatible alias
        $proyekPermissions = [
            // Material & Tool CRUD (full access di gudang proyek)
            'view materials', 'create materials', 'edit materials', 'delete materials',
            'view tools', 'create tools', 'edit tools', 'delete tools',
            'view categories', 'create categories', 'edit categories', 'delete categories',
            // Goods Receipts
            'view goods receipts', 'create goods receipts', 'confirm goods receipts',
            'goods_receipts.create',
            // Material Requests
            'view material requests', 'create material requests', 'material_requests.create',
            // Distributions
            'view distributions', 'create distributions', 'distributions.create',
            'ship distributions', 'distributions.receive', 'receive distributions',
            // Tool Assignments
            'view tool assignments', 'create tool assignments',
            'tools.assign', 'return tool assignments', 'approve tool assignments',
            'tools.inspect_return',
            // Stock Opname
            'view stock opname', 'create stock opname', 'stock_opname.create',
            // Inventory
            'view inventory', 'delete inventory',
            // Procurement
            'create procurement', 'view procurement',
            // Returns
            'create returns', 'view returns', 'receive returns',
            // Reports
            'view reports',
        ];
        $adminProyekRole->syncPermissions($proyekPermissions);
        $userRole->syncPermissions($proyekPermissions);

        // 2.1 Create Karyawan Role
        $karyawanRole = Role::firstOrCreate(['name' => 'Karyawan']);
        $karyawanPermissions = [
            'view materials',
            'view tools',
            'view inventory',
            'view reports',
            'view tool assignments',
            'create tool assignments',
            'view material requests',
            'create material requests',
            'view distributions',
            'create distributions',
            'distributions.create',
        ];
        $karyawanRole->syncPermissions($karyawanPermissions);

        $adminPORole = Role::firstOrCreate(['name' => 'Admin PO']);
        $adminPORole->syncPermissions([
            'view suppliers', 'create suppliers', 'edit suppliers',
            'view materials',
            'view procurement', 'approve procurement',
            'view purchase orders', 'create purchase orders', 'send purchase orders', 'cancel purchase orders',
            'view payments', 'create payments', 'verify payments',
            'view distributions',
            'view reports',
            'audit_logs.view',
        ]);

        // 3. Create Default Central Warehouse & Sample Projects
        $centralWarehouse = Warehouse::firstOrCreate(
            ['code' => 'W-CENTRAL'],
            [
                'name' => 'Gudang Pusat PT Arsikon',
                'type' => 'central',
                'is_central' => true,
                'address' => 'Jl. Industri Utama No. 1, Jakarta',
            ]
        );

        $sampleProjectA = Project::firstOrCreate(
            ['code' => 'PRJ-001'],
            [
                'name' => 'Proyek Pembangunan Gedung A',
                'location' => 'Jakarta Selatan',
                'status' => 'active',
                'start_date' => now()->toDateString(),
            ]
        );

        $projectWarehouseA = Warehouse::firstOrCreate(
            ['code' => 'W-PRJ-001'],
            [
                'project_id' => $sampleProjectA->id,
                'name' => 'Gudang Proyek FK Teknik',
                'type' => 'project',
                'is_central' => false,
                'address' => 'Site Office FK Teknik, Ciamis',
            ]
        );

        $sampleProjectB = Project::firstOrCreate(
            ['code' => 'PRJ-002'],
            [
                'name' => 'Proyek Pembangunan Gedung B',
                'location' => 'Bekasi Timur',
                'status' => 'active',
                'start_date' => now()->toDateString(),
            ]
        );

        $projectWarehouseB = Warehouse::firstOrCreate(
            ['code' => 'W-PRJ-002'],
            [
                'project_id' => $sampleProjectB->id,
                'name' => 'Gudang Proyek Gedung B',
                'type' => 'project',
                'is_central' => false,
                'address' => 'Site Office Gedung B, Bekasi',
            ]
        );

        // 4. Create 5 Initial System Users
        // 1. Owner
        $ownerUser = User::firstOrCreate(
            ['email' => 'owner@arsikon.co.id'],
            [
                'name' => 'Bapak Owner',
                'password' => Hash::make('password123'),
            ]
        );
        $ownerUser->syncRoles([$ownerRole]);
        $ownerUser->warehouses()->syncWithoutDetaching([
            $centralWarehouse->id,
            $projectWarehouseA->id,
            $projectWarehouseB->id,
        ]);

        // 2. Admin Gudang Pusat
        $adminPusatUser = User::firstOrCreate(
            ['email' => 'admin.pusat@arsikon.co.id'],
            [
                'name' => 'Admin Gudang Pusat',
                'password' => Hash::make('password123'),
            ]
        );
        $adminPusatUser->syncRoles([$adminPusatRole, $adminRole]);
        $adminPusatUser->warehouses()->syncWithoutDetaching([$centralWarehouse->id]);

        // 3. Admin Gudang Proyek  (Proyek A)
        $adminProyek1 = User::firstOrCreate(
            ['email' => 'admin.proyek1@arsikon.co.id'],
            [
                'name' => 'Admin Gudang Proyek FK Teknik UGM',
                'password' => Hash::make('password123'),
            ]
        );
        $adminProyek1->syncRoles([$adminProyekRole, $userRole]);
        $adminProyek1->warehouses()->syncWithoutDetaching([$projectWarehouseA->id]);


        // 5. Admin PO (Pengadaan)
        $adminPOUser = User::firstOrCreate(
            ['email' => 'admin.po@arsikon.co.id'],
            [
                'name'     => 'Admin Pengadaan (PO)',
                'password' => Hash::make('password123'),
            ]
        );
        $adminPOUser->syncRoles([$adminPORole]);
        $adminPOUser->warehouses()->syncWithoutDetaching([$centralWarehouse->id]);

        // 6. Karyawan (User Terbatas: View, Pinjam Alat, Request Material, Buat Surat Jalan)
        $karyawanUser = User::firstOrCreate(
            ['email' => 'karyawan@arsikon.co.id'],
            [
                'name'     => 'Pekerja Lapangan',
                'password' => Hash::make('password123'),
            ]
        );
        $karyawanUser->syncRoles([$karyawanRole]);
        $karyawanUser->warehouses()->syncWithoutDetaching([$projectWarehouseA->id]);
    }
}
