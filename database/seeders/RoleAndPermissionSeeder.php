<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\User;
use App\Models\Warehouse;
use App\Support\RolePermissionDefinitions;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Rename canonical roles that changed names so existing data keeps its assignment.
        $roleRenames = [
            'Admin Gudang Pusat' => 'Admin Logistik Pusat',
            'Admin Gudang Proyek' => 'Admin Logistik Proyek',
            'Admin PO' => 'Admin Pengadaan',
            'Admin PO Proyek' => 'Admin Pengadaan Proyek',
        ];
        foreach ($roleRenames as $old => $new) {
            Role::where('name', $old)->update(['name' => $new]);
        }

        // Remove legacy aliases and detached users.
        $legacyRoles = [
            'Admin', 'User', 'Admin Proyek', 'admin-po-nota', 'po_nota_harian',
            'po_lapangan', 'admin_pusat', 'admin_po', 'admin_po_proyek', 'Karyawan', 'karyawan',
        ];
        Role::whereIn('name', $legacyRoles)->each(function ($legacyRole) {
            $legacyRole->users()->detach();
            $legacyRole->permissions()->detach();
            $legacyRole->delete();
        });

        foreach (RolePermissionDefinitions::permissions() as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Owner: read-only / view-only
        $ownerRole = Role::firstOrCreate(['name' => 'Owner']);
        $ownerRole->syncPermissions(
            Permission::where('name', 'like', 'view %')->get()
        );

        // Admin Pusat: full access
        $adminPusatRole = Role::firstOrCreate(['name' => 'Admin Pusat']);
        $adminPusatRole->syncPermissions(Permission::all());

        foreach (['Admin Logistik Pusat', 'Admin Logistik Proyek', 'Site Engineer', 'Site Manager', 'Project Manager', 'Admin Pengadaan', 'Admin Pengadaan Proyek'] as $roleName) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            $permissions = RolePermissionDefinitions::rolePermissions($roleName);
            $role->syncPermissions($permissions ?? []);
        }

        $centralWarehouse = Warehouse::firstOrCreate(
            ['code' => 'W-CENTRAL'],
            [
                'name' => 'Gudang Pusat PT Arsikon',
                'type' => 'central',
                'is_central' => true,
                'address' => 'Jl. Pasir Luhur No.1, Taranan, Sinduharjo, Kec. Ngaglik, Kabupaten Sleman, Daerah Istimewa Yogyakarta 55581',
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
                'address' => 'Site Office FK Teknik, UGM',
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

        $defaultPassword = Hash::make(env('SEED_DEFAULT_PASSWORD', 'password123'));

        $users = [
            'owner@arsikon.co.id' => ['name' => 'Owner Perusahaan', 'role' => 'Owner', 'warehouses' => [$centralWarehouse->id, $projectWarehouseA->id, $projectWarehouseB->id]],
            'admin.pusat@arsikon.co.id' => ['name' => 'Admin Pusat', 'role' => 'Admin Pusat', 'warehouses' => [$centralWarehouse->id, $projectWarehouseA->id, $projectWarehouseB->id]],
            'logistik.pusat@arsikon.co.id' => ['name' => 'Admin Logistik Pusat', 'role' => 'Admin Logistik Pusat', 'warehouses' => [$centralWarehouse->id]],
            'logistik.proyek@arsikon.co.id' => ['name' => 'Admin Logistik Proyek FK Teknik', 'role' => 'Admin Logistik Proyek', 'warehouses' => [$projectWarehouseA->id]],
            'admin.pengadaan@arsikon.co.id' => ['name' => 'Admin Pengadaan Pusat', 'role' => 'Admin Pengadaan', 'warehouses' => [$centralWarehouse->id]],
            'po.proyek@arsikon.co.id' => ['name' => 'Admin Pengadaan Proyek FK Teknik', 'role' => 'Admin Pengadaan Proyek', 'warehouses' => [$projectWarehouseA->id]],
            'engineer.prj1@arsikon.co.id' => ['name' => 'Site Engineer FK Teknik', 'role' => 'Site Engineer', 'warehouses' => [$projectWarehouseA->id]],
            'manager.prj1@arsikon.co.id' => ['name' => 'Site Manager FK Teknik', 'role' => 'Site Manager', 'warehouses' => [$projectWarehouseA->id]],
            'pm.prj1@arsikon.co.id' => ['name' => 'Project Manager FK Teknik', 'role' => 'Project Manager', 'warehouses' => [$projectWarehouseA->id]],
        ];

        foreach ($users as $email => $data) {
            $user = User::updateOrCreate(
                ['email' => $email],
                ['name' => $data['name'], 'password' => $defaultPassword]
            );
            $user->syncRoles([$data['role']]);
            $user->warehouses()->sync($data['warehouses']);
        }

        $legacyAccountMap = [
            'admin.gudang.1@arsikon.co.id' => 'logistik.proyek@arsikon.co.id',
            'admin.gudang.prj2@arsikon.co.id' => 'logistik.proyek@arsikon.co.id',
            'user.proyek@arsikon.co.id' => 'engineer.prj1@arsikon.co.id',
            'karyawan@arsikon.co.id' => 'engineer.prj1@arsikon.co.id',
            'admin.proyek@arsikon.co.id' => 'engineer.prj1@arsikon.co.id',
            'admin.po@arsikon.co.id' => 'admin.pengadaan@arsikon.co.id',
            'admin.po.proyek@arsikon.co.id' => 'po.proyek@arsikon.co.id',
            'admin.proyek1@arsikon.co.id' => 'logistik.proyek@arsikon.co.id',
        ];

        $fkColumns = [
            'distributions' => ['created_by_user_id', 'received_by_user_id', 'shipped_by_user_id'],
            'goods_receipts' => ['confirmed_by', 'created_by', 'received_by_user_id'],
            'maintenances' => ['reported_by_user_id'],
            'material_requests' => ['approved_by_user_id', 'requested_by_user_id'],
            'material_usages' => ['cancelled_by_user_id', 'issued_by_user_id'],
            'payments' => ['created_by', 'verified_by'],
            'procurement_requests' => ['approved_by', 'requested_by'],
            'project_balance_logs' => ['user_id'],
            'project_balance_requests' => ['approved_by', 'requested_by'],
            'purchase_orders' => ['approved_by', 'created_by'],
            'purchase_receipts' => ['created_by'],
            'security_logs' => ['user_id'],
            'stock_mutations' => ['created_by_user_id'],
            'tool_assignments' => ['approved_by_user_id', 'assigned_by_user_id', 'assigned_to_user_id', 'borrower_user_id', 'cancelled_by_user_id'],
            'tool_inspections' => ['inspected_by_user_id'],
            'tool_loans' => ['approved_by_user_id', 'assigned_by_user_id', 'borrower_user_id', 'cancelled_by_user_id'],
            'transit_losses' => ['approved_by', 'recorded_by'],
        ];

        foreach ($legacyAccountMap as $legacyEmail => $replacementEmail) {
            $legacyUser = User::where('email', $legacyEmail)->first();
            $replacementUser = User::where('email', $replacementEmail)->first();
            if ($legacyUser && $replacementUser) {
                foreach ($fkColumns as $table => $columns) {
                    try {
                        if (Schema::hasTable($table)) {
                            foreach ($columns as $column) {
                                if (Schema::hasColumn($table, $column)) {
                                    DB::table($table)
                                        ->where($column, $legacyUser->id)
                                        ->update([$column => $replacementUser->id]);
                                }
                            }
                        }
                    } catch (\Throwable $e) {
                    }
                }

                $legacyUser->roles()->detach();
                $legacyUser->warehouses()->detach();
                try {
                    $legacyUser->delete();
                } catch (\Throwable $e) {
                }
            }
        }
    }
}
