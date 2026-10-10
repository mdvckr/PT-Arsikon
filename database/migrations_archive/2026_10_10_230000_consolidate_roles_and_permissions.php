<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Konsolidasi role: hapus role alias redundan ('Admin' dan 'User')
     * dan migrasikan pengguna ke role kanonikal ('Admin Pusat' dan 'Admin Gudang Proyek').
     */
    public function up(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Pastikan role kanonikal 'Admin Pusat' ada
        $adminPusat = Role::firstOrCreate(['name' => 'Admin Pusat', 'guard_name' => 'web']);
        $adminProyek = Role::firstOrCreate(['name' => 'Admin Gudang Proyek', 'guard_name' => 'web']);

        $legacyAdmin = Role::where('name', 'Admin')->first();
        if ($legacyAdmin) {
            // Pindahkan semua user yang memiliki role 'Admin' ke 'Admin Pusat'
            $usersWithLegacyAdmin = DB::table('model_has_roles')
                ->where('role_id', $legacyAdmin->id)
                ->get();

            foreach ($usersWithLegacyAdmin as $row) {
                DB::table('model_has_roles')->updateOrInsert(
                    [
                        'role_id'    => $adminPusat->id,
                        'model_type' => $row->model_type,
                        'model_id'   => $row->model_id,
                    ]
                );
            }

            // Hapus relasi role lama dan hapus role 'Admin'
            DB::table('model_has_roles')->where('role_id', $legacyAdmin->id)->delete();
            DB::table('role_has_permissions')->where('role_id', $legacyAdmin->id)->delete();
            $legacyAdmin->delete();
        }

        // 2. Konsolidasi role legacy 'User' ke 'Admin Gudang Proyek'
        $legacyUser = Role::where('name', 'User')->first();
        if ($legacyUser) {
            $usersWithLegacyUser = DB::table('model_has_roles')
                ->where('role_id', $legacyUser->id)
                ->get();

            foreach ($usersWithLegacyUser as $row) {
                DB::table('model_has_roles')->updateOrInsert(
                    [
                        'role_id'    => $adminProyek->id,
                        'model_type' => $row->model_type,
                        'model_id'   => $row->model_id,
                    ]
                );
            }

            DB::table('model_has_roles')->where('role_id', $legacyUser->id)->delete();
            DB::table('role_has_permissions')->where('role_id', $legacyUser->id)->delete();
            $legacyUser->delete();
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'User', 'guard_name' => 'web']);
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
