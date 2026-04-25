<?php

namespace App\Providers;

use App\Models\LabResult;
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

        Gate::define('upload-lab-results', function (User $user): bool {
            return \in_array($user->role?->name, ['admin', 'staff', 'lab_technologist', 'radiologist', 'doctor'], true);
        });

        Gate::define('release-lab-results', function (User $user): bool {
            return \in_array($user->role?->name, ['admin', 'staff', 'lab_technologist', 'radiologist', 'doctor'], true);
        });

        Gate::define('view-lab-result', function (User $user, LabResult $labResult): bool {
            $role = $user->role?->name;

            if (\in_array($role, ['admin', 'staff', 'lab_technologist', 'radiologist', 'doctor'], true)) {
                return true;
            }

            if ($role === 'patient') {
                return (int) $labResult->appointment?->user_id === (int) $user->id
                    && $labResult->released_at !== null;
            }

            return false;
        });
    }
}
