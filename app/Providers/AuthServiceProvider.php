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
            // Owner = super admin, izinkan semua ability
            if ($user->hasRole('Owner')) {
                return true;
            }

            // Karyawan: deny ship distributions secara eksplisit
            if ($user->hasRole('Karyawan') && $ability === 'ship distributions') {
                return false;
            }

            // Untuk semua ability lain, delegasikan ke Spatie Permission
            // (return null = lanjut ke permission check berikutnya)
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
