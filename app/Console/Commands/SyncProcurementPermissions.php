<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class SyncProcurementPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:sync-procurement';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronisasi izin akses (permissions) dan peran sistem (roles) untuk alur pengadaan & logistik tanpa migrate:fresh.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Memulai sinkronisasi peran dan izin akses pengadaan & logistik (T16)...');

        // 1. Reset permission cache
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Daftar seluruh canonical permissions
        $permissions = [
            // Users & Management
            'view users', 'create users', 'edit users', 'delete users',
            'projects.manage', 'warehouses.manage',
            // Suppliers & Master Data
            'view suppliers', 'create suppliers', 'edit suppliers', 'delete suppliers',
            'view materials', 'create materials', 'edit materials', 'delete materials',
            'view tools', 'create tools', 'edit tools', 'delete tools',
            'view categories', 'create categories', 'edit categories', 'delete categories',
            // Goods Receipts
            'view goods receipts', 'create goods receipts', 'confirm goods receipts',
            // Material Requests (PO Lapangan)
            'view material requests', 'create material requests', 'approve material requests',
            // Material Usages
            'view material usages', 'create material usages', 'cancel material usages',
            // Distributions (Surat Jalan)
            'view distributions', 'create distributions', 'ship distributions', 'receive distributions',
            // Tool Assignments
            'view tool assignments', 'create tool assignments', 'return tool assignments',
            'approve tool assignments', 'cancel tool assignments', 'inspect return tool assignments',
            // Stock Opname & Inventory
            'view stock opname', 'create stock opname', 'approve stock opname',
            'view inventory', 'delete inventory',
            // Procurement (PR)
            'view procurement', 'create procurement', 'approve procurement',
            // Purchase Orders (PO)
            'view purchase orders', 'create purchase orders', 'edit purchase orders', 'approve purchase orders', 'send purchase orders', 'cancel purchase orders', 'close purchase orders',
            // Purchase Receipts
            'view purchase receipts', 'create purchase receipts', 'delete purchase receipts',
            // Payments
            'view payments', 'create payments', 'verify payments',
            // Returns
            'view returns', 'create returns', 'approve returns', 'receive returns',
            // Transit Losses
            'create transit losses', 'approve transit losses',
            // Reports & Audit Logs
            'view reports', 'view audit logs',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }
        $this->info('Canonical permissions berhasil dipastikan ada (' . count($permissions) . ' permissions).');

        // 3. Sinkronisasi peran sistem (roles)

        // A. Owner: Strictly read-only
        $ownerRole = Role::firstOrCreate(['name' => 'Owner', 'guard_name' => 'web']);
        $ownerRole->syncPermissions(
            Permission::where('name', 'like', 'view %')->get()
        );
        $this->line('  - Role Owner: disinkronkan ke read-only (' . $ownerRole->permissions->count() . ' permissions).');

        // B. Admin Pusat: Full access
        $adminPusatRole = Role::firstOrCreate(['name' => 'Admin Pusat', 'guard_name' => 'web']);
        $adminPusatRole->syncPermissions(Permission::all());
        $this->line('  - Role Admin Pusat: disinkronkan ke full access (' . $adminPusatRole->permissions->count() . ' permissions).');

        // Optional: Admin (bila role Admin lama ada)
        $adminRole = Role::where('name', 'Admin')->first();
        if ($adminRole) {
            $adminRole->syncPermissions(Permission::all());
            $this->line('  - Role Admin: disinkronkan ke full access.');
        }

        // C. Admin PO:
        //    Boleh: create PO, view PO, send PO, cancel PO, create PR, view PR, create payments, view payments,
        //           create transit losses, approve material requests (MR).
        //    HAPUS: approve procurement, verify payments, approve purchase orders, close purchase orders, approve transit losses.
        $adminPORole = Role::firstOrCreate(['name' => 'Admin PO', 'guard_name' => 'web']);
        $adminPOPermissions = [
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
            'view payments', 'create payments',
            'view reports',
            'view audit logs',
        ];
        $adminPORole->syncPermissions($adminPOPermissions);
        $this->line('  - Role Admin PO: disinkronkan (' . count($adminPOPermissions) . ' permissions, tanpa approval PO/PR/Payment).');

        // D. Admin Gudang Pusat:
        //    Boleh: create & confirm GR, create transit losses, operasional logistik sentral.
        $adminGudangPusatRole = Role::firstOrCreate(['name' => 'Admin Gudang Pusat', 'guard_name' => 'web']);
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
            'view reports',
        ];
        $adminGudangPusatRole->syncPermissions($gudangPusatPermissions);
        $this->line('  - Role Admin Gudang Pusat: disinkronkan (' . count($gudangPusatPermissions) . ' permissions).');

        // E. Admin Gudang Proyek: Khusus seluruh operasional logistik fisik gudang proyek
        $adminProyekRole = Role::firstOrCreate(['name' => 'Admin Gudang Proyek', 'guard_name' => 'web']);
        $proyekPermissions = [
            'view distributions', 'create distributions', 'ship distributions', 'receive distributions',
            'view goods receipts', 'create goods receipts', 'confirm goods receipts',
            'view material usages', 'create material usages', 'cancel material usages',
            'view tool assignments', 'create tool assignments', 'return tool assignments',
            'approve tool assignments', 'cancel tool assignments', 'inspect return tool assignments',
            'view inventory',
            'view reports',
            'view materials', 'create materials', 'edit materials',
            'view tools', 'create tools', 'edit tools',
            'view categories', 'create categories',
            'view suppliers', 'create suppliers',
            'create transit losses',
        ];
        $adminProyekRole->syncPermissions($proyekPermissions);
        $this->line('  - Role Admin Gudang Proyek: disinkronkan (' . count($proyekPermissions) . ' permissions).');

        // F. Peran Teknis Lapangan: Admin Proyek, Site Engineer, Site Manager, Project Manager
        $teknisStaffPermissions = [
            'view material requests', 'create material requests',
            'view inventory',
            'view reports',
            'view materials',
            'view tools',
            'view categories',
            'projects.manage',
        ];
        $teknisManagerPermissions = [
            'view material requests', 'create material requests', 'approve material requests',
            'view inventory',
            'view reports',
            'view materials',
            'view tools',
            'view categories',
            'projects.manage',
        ];

        $adminProyekRoleModel = Role::firstOrCreate(['name' => 'Admin Proyek', 'guard_name' => 'web']);
        $adminProyekRoleModel->syncPermissions($teknisStaffPermissions);

        $siteEngineerRole = Role::firstOrCreate(['name' => 'Site Engineer', 'guard_name' => 'web']);
        $siteEngineerRole->syncPermissions($teknisStaffPermissions);

        $siteManagerRole = Role::firstOrCreate(['name' => 'Site Manager', 'guard_name' => 'web']);
        $siteManagerRole->syncPermissions($teknisManagerPermissions);

        $projectManagerRole = Role::firstOrCreate(['name' => 'Project Manager', 'guard_name' => 'web']);
        $projectManagerRole->syncPermissions($teknisManagerPermissions);
        $this->line('  - Role Teknis Lapangan (Admin Proyek, Site Engineer, Site Manager, Project Manager): disinkronkan.');

        // G. Decommission Role Karyawan
        $karyawanRole = Role::where('name', 'Karyawan')->first();
        if ($karyawanRole) {
            foreach ($karyawanRole->users as $user) {
                $user->assignRole($adminProyekRoleModel);
                $user->removeRole($karyawanRole);
            }
            $karyawanRole->permissions()->detach();
            $karyawanRole->delete();
            $this->line('  - Role Karyawan: berhasil didekomisi dan user dialihkan ke Admin Proyek.');
        }

        // Reset cache kembali
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->info('Sinkronisasi izin akses dan peran (T16) berhasil selesai.');

        return Command::SUCCESS;
    }
}
