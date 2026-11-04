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

        // Bersihkan role non-kanonikal lama (Admin, User, Karyawan) jika masih tersisa
        Role::whereIn('name', ['Admin', 'User', 'Karyawan'])->each(function ($legacyRole) {
            $legacyRole->users()->detach();
            $legacyRole->permissions()->detach();
            $legacyRole->delete();
        });

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
            'view purchase orders', 'create purchase orders', 'edit purchase orders', 'approve purchase orders', 'send purchase orders', 'cancel purchase orders', 'close purchase orders',
            // Purchase Receipts
            'view purchase receipts', 'create purchase receipts', 'delete purchase receipts',
            // Payments
            'view payments', 'create payments', 'verify payments',
            // Returns
            'view returns', 'create returns', 'approve returns', 'receive returns',
            // Transit Losses
            'create transit losses', 'approve transit losses',
            // Reports & Audit
            'view reports', 'view audit logs',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // ==========================================================
        // 2. Create Roles & Assign Permissions
        // ==========================================================

        // --- Owner: Eksekutif / Pengawas (Strictly Read-Only / View-Only Laporan & Monitoring) ---
        $ownerRole = Role::firstOrCreate(['name' => 'Owner']);
        $ownerRole->syncPermissions(
            Permission::where('name', 'like', 'view %')->get()
        );

        // --- Admin Pusat: Full Akses Seluruh Sistem, Manajemen Pengguna, Gudang, Jabatan, PO, Logistik ---
        $adminPusatRole = Role::firstOrCreate(['name' => 'Admin Pusat']);
        $adminPusatRole->syncPermissions(Permission::all());

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
            'view inventory',
            'create transit losses',
            'view reports',
        ];
        $adminGudangPusatRole->syncPermissions($gudangPusatPermissions);

        // --- Admin Gudang Proyek (Gudang Proyek): Akses HANYA Seluruh Operasional Logistik Fisik ---
        $adminGudangProyekRole = Role::firstOrCreate(['name' => 'Admin Gudang Proyek']);
        $gudangProyekPermissions = [
            // Surat Jalan & Distribusi Logistik
            'view distributions', 'create distributions', 'ship distributions', 'receive distributions',
            // Penerimaan Barang (Goods Receipts)
            'view goods receipts', 'create goods receipts', 'confirm goods receipts',
            // Permintaan Material (Alur permintaan)
            'view material requests', 'create material requests',
            // Pemakaian Material
            'view material usages', 'create material usages', 'cancel material usages',
            // Peminjaman & Pengembalian Alat
            'view tool assignments', 'create tool assignments', 'return tool assignments',
            'approve tool assignments', 'cancel tool assignments', 'inspect return tool assignments',
            // Inventori & Riwayat Transaksi Logistik
            'view inventory',
            'view reports',
            // Master Data Barang (Read-only untuk operasional logistik)
            'view materials',
            'view tools',
            'view categories',
            'view suppliers',
            // Retur & Kehilangan Pengiriman
            'view returns', 'create returns', 'receive returns',
            'create transit losses',
        ];
        $adminGudangProyekRole->syncPermissions($gudangProyekPermissions);

        // --- Peran Teknis Lapangan: Admin Proyek, Site Engineer, Site Manager, Project Manager ---
        // 1. Staf Teknis Lapangan (Admin Proyek & Site Engineer): Buat & Lihat PO Lapangan, Inventory, Log Harian, Fitur Teknis
        $teknisStaffPermissions = [
            'view material requests', 'create material requests',
            'view inventory',
            'view reports',
            'view materials',
            'view tools',
            'view categories',
            'projects.manage',
        ];

        // 2. Manajemen Teknis Lapangan (Site Manager & Project Manager): Pengawasan, Evaluasi Log Harian, dan Approval PO Lapangan
        $teknisManagerPermissions = [
            'view material requests', 'create material requests', 'approve material requests',
            'view inventory',
            'view reports',
            'view materials',
            'view tools',
            'view categories',
            'projects.manage',
        ];

        $adminProyekRole = Role::firstOrCreate(['name' => 'Admin Proyek']);
        $adminProyekRole->syncPermissions($teknisStaffPermissions);

        $siteEngineerRole = Role::firstOrCreate(['name' => 'Site Engineer']);
        $siteEngineerRole->syncPermissions($teknisStaffPermissions);

        $siteManagerRole = Role::firstOrCreate(['name' => 'Site Manager']);
        $siteManagerRole->syncPermissions($teknisManagerPermissions);

        $projectManagerRole = Role::firstOrCreate(['name' => 'Project Manager']);
        $projectManagerRole->syncPermissions($teknisManagerPermissions);

        // --- Admin PO (Pusat): fokus pengadaan pusat, PO resmi & pembayaran vendor ---
        $adminPORole = Role::firstOrCreate(['name' => 'Admin PO']);
        $adminPORole->syncPermissions([
            'view suppliers', 'create suppliers', 'edit suppliers',
            'view materials',
            'view tools',
            'view inventory',
            'view tool assignments',
            'view stock opname',
            'view material requests', 'approve material requests',
            'view material usages',
            'view procurement', 'create procurement',
            'view purchase orders', 'create purchase orders', 'edit purchase orders', 'send purchase orders', 'cancel purchase orders',
            'view purchase receipts', 'create purchase receipts', 'delete purchase receipts',
            'view payments', 'create payments', 'verify payments',
            'view reports',
            'view audit logs',
        ]);

        // --- Admin PO Proyek: khusus pencatatan nota pembelian harian belanja di proyek & payment proyek ---
        $adminPOProyekRole = Role::firstOrCreate(['name' => 'Admin PO Proyek']);
        $adminPOProyekRole->syncPermissions([
            'view material requests',
            'view purchase receipts',
            'create purchase receipts',
            'delete purchase receipts',
            'view materials',
            'view suppliers',
            'view payments',
            'create payments',
        ]);

        // --- admin-po-nota: Dokumen Perencanaan Bagian 5 (Akses Penuh Nota Harian, Read-Only PO Lapangan) ---
        $adminPONotaRole = Role::firstOrCreate(['name' => 'admin-po-nota']);
        $adminPONotaRole->syncPermissions([
            'view material requests',
            'view purchase receipts',
            'create purchase receipts',
            'delete purchase receipts',
            'view materials',
            'view suppliers',
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
                'address'    => 'Jl. Pasir Luhur No.1, Taranan, Sinduharjo, Kec. Ngaglik, Kabupaten Sleman, Daerah Istimewa Yogyakarta 55581',
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

        // 1. Owner (Super Admin / Monitoring)
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
        $adminPusatUser->syncRoles([$adminPusatRole]);
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

        // 4. Admin Gudang Proyek (Proyek A)
        $adminProyek1 = User::firstOrCreate(
            ['email' => 'admin.gudang.1@arsikon.co.id'],
            [
                'name'     => 'Admin Gudang Proyek FAKULTAS Teknik UGM',
                'password' => Hash::make(env('SEED_DEFAULT_PASSWORD', 'password')),
            ]
        );
        $adminProyek1->update(['password' => Hash::make(env('SEED_DEFAULT_PASSWORD', 'password'))]);
        $adminProyek1->syncRoles([$adminGudangProyekRole]);
        $adminProyek1->warehouses()->syncWithoutDetaching([$projectWarehouseA->id]);

        // 5. User Proyek / Admin Proyek (Proyek A)
        $userProyek = User::firstOrCreate(
            ['email' => 'admin.proyek@arsikon.co.id'],
            [
                'name'     => 'User Proyek FAKULTAS Teknik UGM',
                'password' => Hash::make(env('SEED_DEFAULT_PASSWORD', 'password123')),
            ]
        );
        $userProyek->syncRoles([$adminProyekRole]);
        $userProyek->warehouses()->syncWithoutDetaching([$projectWarehouseA->id]);

        // 6. Admin PO (Pengadaan)
        $adminPOUser = User::firstOrCreate(
            ['email' => 'admin.po@arsikon.co.id'],
            [
                'name'     => 'Admin Pengadaan (PO)',
                'password' => Hash::make(env('SEED_DEFAULT_PASSWORD', 'password123')),
            ]
        );
        $adminPOUser->syncRoles([$adminPORole]);
        $adminPOUser->warehouses()->syncWithoutDetaching([$centralWarehouse->id]);

        // 7. Pengalihan Akun Pekerja Lapangan (Eks-Karyawan) -> Site Engineer Proyek A
        $karyawanUser = User::firstOrCreate(
            ['email' => 'karyawan@arsikon.co.id'],
            [
                'name'     => 'Site Engineer FAKULTAS Teknik UGM',
                'password' => Hash::make(env('SEED_DEFAULT_PASSWORD', 'password123')),
            ]
        );
        $karyawanUser->syncRoles([$siteEngineerRole]);
        $karyawanUser->warehouses()->syncWithoutDetaching([$projectWarehouseA->id]);

        // 8. Admin PO Proyek (Khusus Nota Harian di Proyek FK Teknik)
        $adminPOProyekUser = User::firstOrCreate(
            ['email' => 'po.proyek@arsikon.co.id'],
            [
                'name'     => 'Admin PO Proyek (Nota Harian)',
                'password' => Hash::make(env('SEED_DEFAULT_PASSWORD', 'password123')),
            ]
        );
        $adminPOProyekUser->syncRoles([$adminPOProyekRole]);
        $adminPOProyekUser->warehouses()->syncWithoutDetaching([$projectWarehouseA->id]);
    }
}
