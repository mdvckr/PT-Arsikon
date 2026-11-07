<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
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

        // Bersihkan role non-kanonikal lama jika masih tersisa
        Role::whereIn('name', ['Admin', 'User', 'Admin Proyek', 'Admin Logistik Proyek', 'Admin Pengadaan', 'Admin Pengadaan Proyek'])->each(function ($legacyRole) {
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
            // Transit Losses
            'create transit losses', 'approve transit losses',
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
        $adminPusatRole->syncPermissions(
            Permission::all()
        );

        // --- Admin Gudang Pusat: Khusus Operasional Logistik Pergudangan Sentral ---
        $adminGudangPusatRole = Role::firstOrCreate(['name' => 'Admin Gudang Pusat']);
        $gudangPusatPermissions = [
            'view suppliers', 'create suppliers', 'edit suppliers',
            'view materials', 'create materials', 'edit materials', 'delete materials',
            'view tools', 'create tools', 'edit tools', 'delete tools',
            'view categories', 'create categories', 'edit categories', 'delete categories',
            'view goods receipts', 'create goods receipts', 'confirm goods receipts',
            'view material usages', 'create material usages', 'cancel material usages',
            'view distributions', 'create distributions', 'ship distributions', 'receive distributions',
            'view tool assignments', 'create tool assignments', 'return tool assignments',
            'approve tool assignments', 'cancel tool assignments', 'inspect return tool assignments',
            'view inventory',
            'create transit losses',
        ];
        $adminGudangPusatRole->syncPermissions($gudangPusatPermissions);

        // --- Admin Gudang Proyek (Gudang Proyek): Akses HANYA Seluruh Operasional Logistik Fisik ---
        $adminGudangProyekRole = Role::firstOrCreate(['name' => 'Admin Gudang Proyek']);
        $gudangProyekPermissions = [
            // Surat Jalan & Distribusi Logistik
            'view distributions', 'create distributions', 'ship distributions', 'receive distributions',
            // Penerimaan Barang (Goods Receipts)
            'view goods receipts', 'create goods receipts', 'confirm goods receipts',
            // Pemakaian Material
            'view material usages', 'create material usages', 'cancel material usages',
            // Peminjaman & Pengembalian Alat
            'view tool assignments', 'create tool assignments', 'return tool assignments',
            'approve tool assignments', 'cancel tool assignments', 'inspect return tool assignments',
            // Inventori & Riwayat Transaksi Logistik
            'view inventory',
            // Master Data Barang (Kelola & Tambah Material/Alat)
            'view materials', 'create materials', 'edit materials',
            'view tools', 'create tools', 'edit tools',
            'view categories', 'create categories',
            'view suppliers', 'create suppliers',
            // Kehilangan Pengiriman
            'create transit losses',
        ];
        $adminGudangProyekRole->syncPermissions($gudangProyekPermissions);

        // --- Peran Teknis Lapangan: Site Engineer, Site Manager, Project Manager ---
        // 1. Staf Teknis Lapangan (Site Engineer): Buat & Lihat PO Lapangan, Inventory, Log Harian, Fitur Teknis
        $teknisStaffPermissions = [
            'view material requests', 'create material requests',
            'view inventory',
            'view materials',
            'view tools',
            'view categories',
            'projects.manage',
        ];

        // 2. Manajemen Teknis Lapangan (Site Manager & Project Manager): Pengawasan, Evaluasi Log Harian, Approval PO Lapangan, Penerimaan Barang
        $teknisManagerPermissions = [
            'view goods receipts', 'create goods receipts', 'confirm goods receipts',
            'view material requests', 'create material requests', 'approve material requests',
            'view inventory',
            'view materials',
            'view tools',
            'view categories',
            'projects.manage',
        ];

        $siteEngineerRole = Role::firstOrCreate(['name' => 'Site Engineer']);
        $siteEngineerRole->syncPermissions($teknisStaffPermissions);

        $siteManagerRole = Role::firstOrCreate(['name' => 'Site Manager']);
        $siteManagerRole->syncPermissions($teknisManagerPermissions);

        $projectManagerRole = Role::firstOrCreate(['name' => 'Project Manager']);
        $projectManagerRole->syncPermissions($teknisManagerPermissions);

        // --- Karyawan: akses terbatas (lihat stok, pinjam alat, request material/PO Lapangan) ---
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
        ]);

        // --- Admin PO (Pusat): fokus pengadaan pusat, PO resmi & pembayaran vendor ---
        $adminPORole = Role::firstOrCreate(['name' => 'Admin PO']);
        $adminPORole->syncPermissions([
            'view suppliers', 'create suppliers', 'edit suppliers',
            'view materials',
            'view tools',
            'view inventory',
            'view tool assignments',
            'view material requests', 'approve material requests',
            'view material usages',
            'view procurement', 'create procurement',
            'view purchase orders', 'create purchase orders', 'edit purchase orders', 'send purchase orders', 'cancel purchase orders',
            'view purchase receipts', 'create purchase receipts', 'delete purchase receipts',
            'view payments', 'create payments', 'verify payments',
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
        // 4. Buat / Perbarui 9 Akun Testing Resmi PT Arsikon
        // ==========================================================
        $defaultPassword = Hash::make(env('SEED_DEFAULT_PASSWORD', 'password123'));

        // 1. Owner Perusahaan
        $ownerUser = User::updateOrCreate(
            ['email' => 'owner@arsikon.co.id'],
            [
                'name'     => 'Owner Perusahaan',
                'password' => $defaultPassword,
            ]
        );
        $ownerUser->syncRoles([$ownerRole]);
        $ownerUser->warehouses()->sync([
            $centralWarehouse->id,
            $projectWarehouseA->id,
            $projectWarehouseB->id,
        ]);

        // 2. Admin Pusat
        $adminPusatUser = User::updateOrCreate(
            ['email' => 'admin.pusat@arsikon.co.id'],
            [
                'name'     => 'Admin Pusat',
                'password' => $defaultPassword,
            ]
        );
        $adminPusatUser->syncRoles([$adminPusatRole]);
        $adminPusatUser->warehouses()->sync([
            $centralWarehouse->id,
            $projectWarehouseA->id,
            $projectWarehouseB->id,
        ]);

        // 3. Admin Gudang Pusat
        $adminGudangPusatUser = User::updateOrCreate(
            ['email' => 'gudang.pusat@arsikon.co.id'],
            [
                'name'     => 'Admin Gudang Pusat',
                'password' => $defaultPassword,
            ]
        );
        $adminGudangPusatUser->syncRoles([$adminGudangPusatRole]);
        $adminGudangPusatUser->warehouses()->sync([$centralWarehouse->id]);

        // 4. Admin Gudang Proyek FK Teknik
        $adminGudangPrjUser = User::updateOrCreate(
            ['email' => 'admin.gudang.prj1@arsikon.co.id'],
            [
                'name'     => 'Admin Gudang Proyek FK Teknik',
                'password' => $defaultPassword,
            ]
        );
        $adminGudangPrjUser->syncRoles([$adminGudangProyekRole]);
        $adminGudangPrjUser->warehouses()->sync([$projectWarehouseA->id]);

        // 5. Admin Pengadaan Pusat
        $adminPOUser = User::updateOrCreate(
            ['email' => 'admin.po@arsikon.co.id'],
            [
                'name'     => 'Admin Pengadaan Pusat',
                'password' => $defaultPassword,
            ]
        );
        $adminPOUser->syncRoles([$adminPORole]);
        $adminPOUser->warehouses()->sync([$centralWarehouse->id]);

        // 6. Admin Pembelian Proyek FK Teknik
        $adminPOProyekUser = User::updateOrCreate(
            ['email' => 'po.proyek@arsikon.co.id'],
            [
                'name'     => 'Admin Pembelian Proyek FK Teknik',
                'password' => $defaultPassword,
            ]
        );
        $adminPOProyekUser->syncRoles([$adminPOProyekRole]);
        $adminPOProyekUser->warehouses()->sync([$projectWarehouseA->id]);

        // 7. Site Engineer FK Teknik
        $siteEngineerUser = User::updateOrCreate(
            ['email' => 'engineer.prj1@arsikon.co.id'],
            [
                'name'     => 'Site Engineer FK Teknik',
                'password' => $defaultPassword,
            ]
        );
        $siteEngineerUser->syncRoles([$siteEngineerRole]);
        $siteEngineerUser->warehouses()->sync([$projectWarehouseA->id]);

        // 8. Site Manager FK Teknik
        $siteManagerUser = User::updateOrCreate(
            ['email' => 'manager.prj1@arsikon.co.id'],
            [
                'name'     => 'Site Manager FK Teknik',
                'password' => $defaultPassword,
            ]
        );
        $siteManagerUser->syncRoles([$siteManagerRole]);
        $siteManagerUser->warehouses()->sync([$projectWarehouseA->id]);

        // 9. Project Manager FK Teknik
        $projectManagerUser = User::updateOrCreate(
            ['email' => 'pm.prj1@arsikon.co.id'],
            [
                'name'     => 'Project Manager FK Teknik',
                'password' => $defaultPassword,
            ]
        );
        $projectManagerUser->syncRoles([$projectManagerRole]);
        $projectManagerUser->warehouses()->sync([$projectWarehouseA->id]);

        // ==========================================================
        // 5. Pembersihan Akun Testing Lama & Pengalihan Relasi Data
        // ==========================================================
        $legacyAccountMap = [
            'admin.proyek1@arsikon.co.id'     => $adminGudangPrjUser->id,
            'admin.gudang.1@arsikon.co.id'    => $adminGudangPrjUser->id,
            'admin.gudang.prj2@arsikon.co.id' => $adminGudangPrjUser->id,
            'user.proyek@arsikon.co.id'       => $siteEngineerUser->id,
            'karyawan@arsikon.co.id'          => $siteEngineerUser->id,
            'admin.proyek@arsikon.co.id'      => $siteEngineerUser->id,
        ];

        $fkColumns = [
            'distributions'             => ['created_by_user_id', 'received_by_user_id', 'shipped_by_user_id'],
            'goods_receipts'            => ['confirmed_by', 'created_by', 'received_by_user_id'],
            'maintenances'              => ['reported_by_user_id'],
            'material_requests'         => ['approved_by_user_id', 'requested_by_user_id'],
            'material_usages'           => ['cancelled_by_user_id', 'issued_by_user_id'],
            'payments'                  => ['created_by', 'verified_by'],
            'print_templates'           => ['created_by'],
            'procurement_requests'      => ['approved_by', 'requested_by'],
            'project_balance_logs'      => ['user_id'],
            'project_balance_requests'  => ['approved_by', 'requested_by'],
            'purchase_orders'           => ['approved_by', 'created_by'],
            'purchase_receipts'         => ['created_by'],
            'security_logs'             => ['user_id'],
            'stock_mutations'           => ['created_by_user_id'],
            'tool_assignments'          => ['approved_by_user_id', 'assigned_by_user_id', 'assigned_to_user_id', 'borrower_user_id', 'cancelled_by_user_id'],
            'tool_inspections'          => ['inspected_by_user_id'],
            'tool_loans'                => ['approved_by_user_id', 'assigned_by_user_id', 'borrower_user_id', 'cancelled_by_user_id'],
            'transit_losses'            => ['approved_by', 'recorded_by'],
        ];

        foreach ($legacyAccountMap as $legacyEmail => $replacementUserId) {
            $legacyUser = User::where('email', $legacyEmail)->first();
            if ($legacyUser) {
                // Alihkan relasi data transaksi ke akun testing yang baru agar tidak melanggar foreign key
                foreach ($fkColumns as $table => $columns) {
                    try {
                        if (Schema::hasTable($table)) {
                            foreach ($columns as $column) {
                                if (Schema::hasColumn($table, $column)) {
                                    DB::table($table)
                                        ->where($column, $legacyUser->id)
                                        ->update([$column => $replacementUserId]);
                                }
                            }
                        }
                    } catch (\Throwable $e) {
                        // Abaikan jika query per tabel mengalami pengecualian
                    }
                }

                $legacyUser->roles()->detach();
                $legacyUser->warehouses()->detach();
                try {
                    $legacyUser->delete();
                } catch (\Throwable $e) {
                    // Abaikan jika masih tertahan constraint khusus
                }
            }
        }
    }
}