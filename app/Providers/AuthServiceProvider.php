<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        /*
         * Owner = super admin (semua ability diizinkan).
         *
         * Ability approval didelegasikan ke permission Spatie agar BUKAN user biasa
         * (misal role "User") yang bisa menyetujui permintaan mereka sendiri —
         * hanya Admin/role yang memiliki permission 'approve ...' yang berhak.
         *
         * Ability lain diizinkan (perilaku lama) supaya tidak timbul 403 tak terduga.
         */
        Gate::before(function ($user, $ability) {
            /*
             * Owner = super admin (semua ability diizinkan).
             */
            if ($user->hasRole('Owner')) {
                return true;
            }

            // Proses Pengiriman (Ship) hanya untuk Admin / Pengelola Gudang, bukan Karyawan
            if ($ability === 'ship distributions') {
                if ($user->hasRole('Karyawan')) {
                    return false;
                }
                if ($user->hasAnyRole(['Admin', 'Admin Gudang Pusat', 'Admin Gudang Proyek'])) {
                    return true;
                }
                return null;
            }

            // Ability approval & konfirmasi didelegasikan ke permission Spatie
            if (str_starts_with($ability, 'approve ') || str_starts_with($ability, 'confirm ')) {
                return null; // serahkan ke permission Spatie
            }

            // Role Karyawan tidak boleh melakukan aksi administratif penting
            if ($user->hasRole('Karyawan')) {
                if (in_array($ability, ['ship distributions', 'delete materials', 'delete tools', 'delete inventory'])) {
                    return false;
                }
            }

            // Admin & Admin Gudang mendapatkan akses penuh untuk material & tools CRUD
            if ($user->hasAnyRole(['Admin', 'Admin Gudang Pusat', 'Admin Gudang Proyek', 'Admin PO'])) {
                $adminAbilities = [
                    'create materials', 'view materials', 'edit materials', 'delete materials',
                    'create tools', 'view tools', 'edit tools', 'delete tools',
                    'create categories', 'edit categories', 'delete categories',
                    'create suppliers', 'edit suppliers', 'delete suppliers',
                    'manage warehouses', 'manage projects', 'manage users',
                    'create stock opname', 'view stock opname',
                    'create goods receipts', 'confirm goods receipts',
                    'create material requests', 'approve material requests',
                    'create distributions', 'ship distributions', 'receive distributions',
                    'create tool assignments', 'approve tool assignments',
                    'create procurement', 'approve procurement',
                    'create purchase orders', 'cancel purchase orders',
                    'create purchase receipts', 'delete purchase receipts',
                    'create payments', 'verify payments',
                    'create returns', 'approve returns', 'receive returns',
                    'create material usages', 'cancel material usages',
                    'create users', 'edit users', 'delete users',
                    'view reports', 'view inventory', 'view audit logs',
                    'manage suppliers', 'manage projects', 'manage warehouses', 'manage users',
                ];
                if (in_array($ability, $adminAbilities)) {
                    return true;
                }
            }

            return null;
        });
    }
}
