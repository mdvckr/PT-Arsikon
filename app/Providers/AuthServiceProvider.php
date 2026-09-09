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
            if ($user->hasRole('Owner')) {
                return true;
            }

            if (str_starts_with($ability, 'approve ') || str_starts_with($ability, 'confirm ')) {
                return null; // serahkan ke permission Spatie
            }

            return true;
        });
    }
}
