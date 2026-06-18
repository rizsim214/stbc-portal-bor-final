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
            return $user->isAdmin();
        });

        Gate::define('manage-roles', function (User $user): bool {
            return $user->isAdmin();
        });

        Gate::define('book-appointments', function (User $user): bool {
            return $user->hasRole('user');
        });

        Gate::define('upload-lab-results', function (User $user): bool {
            return $user->isAdmin();
        });

        Gate::define('release-lab-results', function (User $user): bool {
            return $user->isAdmin();
        });

        Gate::define('view-lab-result', function (User $user, LabResult $labResult): bool {
            if ($user->isAdmin()) {
                return true;
            }

            if ($user->hasRole('user')) {
                return (int) $labResult->appointment?->user_id === (int) $user->id
                    && $labResult->released_at !== null;
            }

            return false;
        });

        Gate::define('view-medical-history', function (User $user, User $targetUser): bool {
            if ($user->isAdmin()) {
                return true;
            }

            return $user->hasRole('user') && (int) $user->id === (int) $targetUser->id;
        });
    }
}
