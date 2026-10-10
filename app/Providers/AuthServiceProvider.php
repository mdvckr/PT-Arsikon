<?php

namespace App\Providers;

use App\Models\MaterialUsage;
use App\Models\User;
use App\Models\Warehouse;
use App\Policies\MaterialUsagePolicy;
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
        MaterialUsage::class => MaterialUsagePolicy::class,
        \Spatie\Permission\Models\Role::class => \App\Policies\RolePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * Strategi Otorisasi:
     * - Owner  : super admin, semua ability diizinkan via Gate::before().
     * - Role lain : bergantung pada permission Spatie yang di-assign di seeder.
     *   Gate::before() hanya menangani logika yang TIDAK bisa ditangani Spatie secara bersih:
     *   (1) Override khusus Karyawan (deny ship distributions).
     *   (2) Bypass untuk Admin Gudang Pusat/Proyek pada 'ship distributions'.
     * Semua ability lain DIDELEGASIKAN ke Spatie Permission agar satu-satunya
     * source of truth ada di seeder, bukan tersebar di dua tempat.
     */
    public function boot(): void
    {
        Gate::before(function (User $user, string $ability) {
            // 1. Admin Pusat: Full Access ke seluruh modul, approval, dan aksi sistem
            if ($user->hasRole('Admin Pusat')) {
                return true;
            }

            // 2. Owner: Strictly Read-Only / View-Only (Laporan & Monitoring Eksekutif)
            // Owner DILARANG melakukan penambahan, pengeditan, penghapusan, ataupun approval/reject
            if ($user->hasRole('Owner')) {
                // Tangkal aksi mutasi dan persetujuan
                if (preg_match('/^(create|edit|update|delete|destroy|approve|reject|confirm|ship|receive|verify|cancel|manage|\w+\.manage)/i', $ability)) {
                    return false;
                }
                // Izinkan seluruh aksi melihat data dan laporan
                if (str_starts_with($ability, 'view')) {
                    return true;
                }
                return false;
            }

            // 3. Resolusi Dual-Role: Jika user memiliki role Karyawan DAN Admin Gudang,
            // hak wewenang Admin Gudang diutamakan agar operasional pengiriman (ship distributions) tidak terblokir
            if ($user->hasRole('Karyawan') && !$user->hasAnyRole(['Admin Pusat', 'Admin Gudang Pusat', 'Admin Gudang Proyek']) && $ability === 'ship distributions') {
                return false;
            }

            // Untuk aksi lain, delegasikan ke Spatie Permission
            return null;
        });

        // Gate untuk bypass approval MaterialUsage di gudang proyek
        Gate::define('bypass-material-usage-approval', function (User $user, Warehouse $warehouse) {
            return $user->hasRole('Admin Gudang Proyek')
                && !$warehouse->isCentral()
                && $user->hasAccessToWarehouse($warehouse);
        });
    }
}
