<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('manage-users', function (User $user): bool {
            return $user->role?->name === 'admin';
        });

        Gate::define('manage-roles', function (User $user): bool {
            return $user->role?->name === 'admin';
        });

        Gate::define('update-own-staff-status', function (User $user): bool {
            return \in_array($user->role?->name, ['doctor', 'radiologist', 'lab_technologist', 'staff'], true);
        });
    }
}
