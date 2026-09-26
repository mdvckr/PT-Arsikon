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
     *
     * Konvensi Penamaan Permission: format "kata kerja + objek" dengan spasi.
     * Contoh: 'view materials', 'create distributions', 'approve returns'.
     * Permission alias format dot (goods_receipts.create, dll.) telah dihapus
     * karena duplikat — gunakan format spasi sebagai satu-satunya sumber kebenaran.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // ==========================================================
        // 1. Define Canonical Permissions (format spasi, tanpa duplikat)
        // ==========================================================
        $permissions = [
            // Users
            'view users', 'create users', 'edit users', 'delete users',
            // Projects & Warehouses
            'projects.manage',
            'warehouses.manage',
            // Suppliers
            'view suppliers', 'create suppliers', 'edit suppliers', 'delete suppliers',
            // Master Data
            'view materials', 'create materials', 'edit materials', 'delete materials',
            'view tools', 'create tools', 'edit tools', 'delete tools',
            // Categories & Units
            'view categories', 'create categories', 'edit categories', 'delete categories',
            // Goods Receipts
            'view goods receipts', 'create goods receipts', 'confirm goods receipts',
            // Material Requests
            'view material requests', 'create material requests', 'approve material requests',
            // Material Usages
            'view material usages', 'create material usages', 'cancel material usages',
            // Distributions
            'view distributions', 'create distributions', 'ship distributions', 'receive distributions',
            // Tool Assignments
            'view tool assignments', 'create tool assignments', 'return tool assignments',
            'approve tool assignments', 'cancel tool assignments', 'inspect return tool assignments',
            // Stock Opname
            'view stock opname', 'create stock opname', 'approve stock opname',
            // Inventory
            'view inventory', 'delete inventory',
            // Procurement
            'view procurement', 'create procurement', 'approve procurement',
            // Purchase Orders
            'view purchase orders', 'create purchase orders', 'send purchase orders', 'cancel purchase orders',
            // Purchase Receipts
            'view purchase receipts', 'create purchase receipts', 'delete purchase receipts',
            // Payments
            'view payments', 'create payments', 'verify payments',
            // Returns
            'view returns', 'create returns', 'approve returns', 'receive returns',
            // Reports & Audit
            'view reports', 'view audit logs',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // ==========================================================
        // 2. Create Roles & Assign Permissions
        // ==========================================================

        // --- Owner: Super Admin (semua permission) ---
        $ownerRole = Role::firstOrCreate(['name' => 'Owner']);
        $ownerRole->syncPermissions(Permission::all());

        // --- Admin Pusat: Full Akses Seluruh Sistem, Manajemen Pengguna, Gudang, Jabatan, PO, Logistik ---
        $adminPusatRole = Role::firstOrCreate(['name' => 'Admin Pusat']);
        $adminRole      = Role::firstOrCreate(['name' => 'Admin']); // alias kompatibilitas
        $adminPusatRole->syncPermissions(Permission::all());
        $adminRole->syncPermissions(Permission::all());

        // --- Admin Gudang Pusat: Khusus Operasional Logistik Pergudangan Sentral ---
        $adminGudangPusatRole = Role::firstOrCreate(['name' => 'Admin Gudang Pusat']);
        $gudangPusatPermissions = [
            'view suppliers',
            'view materials', 'create materials', 'edit materials', 'delete materials',
            'view tools', 'create tools', 'edit tools', 'delete tools',
            'view categories', 'create categories', 'edit categories', 'delete categories',
            'view goods receipts', 'create goods receipts', 'confirm goods receipts',
            'view material requests', 'create material requests', 'approve material requests',
            'view material usages', 'create material usages', 'cancel material usages',
            'view distributions', 'create distributions', 'ship distributions', 'receive distributions',
            'view tool assignments', 'create tool assignments', 'return tool assignments',
            'approve tool assignments', 'cancel tool assignments', 'inspect return tool assignments',
            'view stock opname', 'create stock opname', 'approve stock opname',
            'view inventory',
            'view returns', 'approve returns', 'receive returns',
            'view reports',
        ];
        $adminGudangPusatRole->syncPermissions($gudangPusatPermissions);

        // --- Admin Gudang Proyek (+ alias 'User' untuk backward compat) ---
        $adminProyekRole = Role::firstOrCreate(['name' => 'Admin Gudang Proyek']);
        $userRole        = Role::firstOrCreate(['name' => 'User']); // backward-compatible alias
        $proyekPermissions = [
            // Material & Tool CRUD
            'view materials', 'create materials', 'edit materials', 'delete materials',
            'view tools', 'create tools', 'edit tools', 'delete tools',
            'view categories', 'create categories', 'edit categories', 'delete categories',
            // Goods Receipts
            'view goods receipts', 'create goods receipts', 'confirm goods receipts',
            // Material Requests
            'view material requests', 'create material requests',
            // Material Usages
            'view material usages', 'create material usages', 'cancel material usages',
            // Distributions
            'view distributions', 'create distributions', 'ship distributions', 'receive distributions',
            // Tool Assignments
            'view tool assignments', 'create tool assignments', 'return tool assignments',
            'approve tool assignments', 'inspect return tool assignments', 'cancel tool assignments',
            // Stock Opname
            'view stock opname', 'create stock opname',
            // Inventory
            'view inventory', 'delete inventory',
            // Procurement
            'view procurement', 'create procurement',
            // Returns
            'view returns', 'create returns', 'receive returns',
            // Reports
            'view reports',
        ];
        $adminProyekRole->syncPermissions($proyekPermissions);
        $userRole->syncPermissions($proyekPermissions);

        // --- Karyawan: akses terbatas (lihat stok, pinjam alat, request material, buat surat jalan) ---
        $karyawanRole = Role::firstOrCreate(['name' => 'Karyawan']);
        $karyawanRole->syncPermissions([
            'view materials',
            'view tools',
            'view inventory',
            'view tool assignments',
            'create tool assignments',
            'view material requests',
            'create material requests',
            'view material usages',
            'view distributions',
            'create distributions',
        ]);

        // --- Admin PO: fokus pengadaan & pembelian ---
        $adminPORole = Role::firstOrCreate(['name' => 'Admin PO']);
        $adminPORole->syncPermissions([
            'view suppliers', 'create suppliers', 'edit suppliers',
            'view materials',
            'view tools',
            'view inventory',
            'view tool assignments',
            'view stock opname',
            'view material requests',
            'view material usages',
            'view procurement', 'approve procurement',
            'view purchase orders', 'create purchase orders', 'send purchase orders', 'cancel purchase orders',
            'view purchase receipts', 'create purchase receipts', 'delete purchase receipts',
            'view payments', 'create payments', 'verify payments',
            'view distributions', 'create distributions', 'ship distributions', 'receive distributions',
            'view reports',
            'view audit logs',
        ]);

        // ==========================================================
        // 3. Create Default Central Warehouse & Sample Projects
        // ==========================================================
        $centralWarehouse = Warehouse::firstOrCreate(
            ['code' => 'W-CENTRAL'],
            [
                'name'       => 'Gudang Pusat PT Arsikon',
                'type'       => 'central',
                'is_central' => true,
                'address'    => 'Jl. Industri Utama No. 1, Jakarta',
            ]
        );

        $sampleProjectA = Project::firstOrCreate(
            ['code' => 'PRJ-001'],
            [
                'name'       => 'Proyek Pembangunan Gedung A',
                'location'   => 'Jakarta Selatan',
                'status'     => 'active',
                'start_date' => now()->toDateString(),
            ]
        );

        $projectWarehouseA = Warehouse::firstOrCreate(
            ['code' => 'W-PRJ-001'],
            [
                'project_id' => $sampleProjectA->id,
                'name'       => 'Gudang Proyek FK Teknik',
                'type'       => 'project',
                'is_central' => false,
                'address'    => 'Site Office FK Teknik, Ciamis',
            ]
        );

        $sampleProjectB = Project::firstOrCreate(
            ['code' => 'PRJ-002'],
            [
                'name'       => 'Proyek Pembangunan Gedung B',
                'location'   => 'Bekasi Timur',
                'status'     => 'active',
                'start_date' => now()->toDateString(),
            ]
        );

        $projectWarehouseB = Warehouse::firstOrCreate(
            ['code' => 'W-PRJ-002'],
            [
                'project_id' => $sampleProjectB->id,
                'name'       => 'Gudang Proyek Gedung B',
                'type'       => 'project',
                'is_central' => false,
                'address'    => 'Site Office Gedung B, Bekasi',
            ]
        );

        // ==========================================================
        // 4. Create Default System Users
        // ==========================================================

        // 1. Owner
        $ownerUser = User::firstOrCreate(
            ['email' => 'owner@arsikon.co.id'],
            [
                'name'     => 'Bapak Owner',
                'password' => Hash::make(env('SEED_DEFAULT_PASSWORD', 'password')),
            ]
        );
        $ownerUser->syncRoles([$ownerRole]);
        $ownerUser->warehouses()->syncWithoutDetaching([
            $centralWarehouse->id,
            $projectWarehouseA->id,
            $projectWarehouseB->id,
        ]);

        // 2. Admin Pusat (Full Akses & Kelola Akun/Gudang/Jabatan)
        $adminPusatUser = User::firstOrCreate(
            ['email' => 'admin.pusat@arsikon.co.id'],
            [
                'name'     => 'Admin Pusat',
                'password' => Hash::make(env('SEED_DEFAULT_PASSWORD', 'password')),
            ]
        );
        $adminPusatUser->update(['name' => 'Admin Pusat']);
        $adminPusatUser->syncRoles([$adminPusatRole, $adminRole]);
        $adminPusatUser->warehouses()->syncWithoutDetaching([
            $centralWarehouse->id,
            $projectWarehouseA->id,
            $projectWarehouseB->id,
        ]);

        // 3. Admin Gudang Pusat (Hanya Akses Logistik)
        $adminGudangPusatUser = User::firstOrCreate(
            ['email' => 'gudang.pusat@arsikon.co.id'],
            [
                'name'     => 'Admin Gudang Pusat',
                'password' => Hash::make(env('SEED_DEFAULT_PASSWORD', 'password')),
            ]
        );
        $adminGudangPusatUser->update(['name' => 'Admin Gudang Pusat']);
        $adminGudangPusatUser->syncRoles([$adminGudangPusatRole]);
        $adminGudangPusatUser->warehouses()->syncWithoutDetaching([$centralWarehouse->id]);

        // 3. Admin Gudang Proyek (Proyek A)
        $adminProyek1 = User::firstOrCreate(
            ['email' => 'admin.proyek1@arsikon.co.id'],
            [
                'name'     => 'Admin Gudang Proyek FAKULTAS Teknik UGM',
                'password' => Hash::make(env('SEED_DEFAULT_PASSWORD', 'password123')),
            ]
        );
        $adminProyek1->syncRoles([$adminProyekRole, $userRole]);
        $adminProyek1->warehouses()->syncWithoutDetaching([$projectWarehouseA->id]);

        // 4. User Proyek (Proyek A)
        $userProyek = User::firstOrCreate(
            ['email' => 'user.proyek@arsikon.co.id'],
            [
                'name'     => 'User Proyek FAKULTAS Teknik UGM',
                'password' => Hash::make(env('SEED_DEFAULT_PASSWORD', 'password123')),
            ]
        );
        $userProyek->syncRoles([$userRole, $adminProyekRole]);
        $userProyek->warehouses()->syncWithoutDetaching([$projectWarehouseA->id]);

        // 5. Admin PO (Pengadaan)
        $adminPOUser = User::firstOrCreate(
            ['email' => 'admin.po@arsikon.co.id'],
            [
                'name'     => 'Admin Pengadaan (PO)',
                'password' => Hash::make(env('SEED_DEFAULT_PASSWORD', 'password123')),
            ]
        );
        $adminPOUser->syncRoles([$adminPORole]);
        $adminPOUser->warehouses()->syncWithoutDetaching([$centralWarehouse->id]);

        // 6. Karyawan (akses terbatas)
        $karyawanUser = User::firstOrCreate(
            ['email' => 'karyawan@arsikon.co.id'],
            [
                'name'     => 'Pekerja Lapangan',
                'password' => Hash::make(env('SEED_DEFAULT_PASSWORD', 'password123')),
            ]
        );
        $karyawanUser->syncRoles([$karyawanRole]);
        $karyawanUser->warehouses()->syncWithoutDetaching([$projectWarehouseA->id]);
    }
}
