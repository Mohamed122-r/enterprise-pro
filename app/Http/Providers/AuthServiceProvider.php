<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        // Define custom gates based on permissions
        Gate::before(function ($user, $ability) {
            if ($user->hasPermissionTo($ability)) {
                return true;
            }
        });

        // Define specific gates
        Gate::define('access-admin', function ($user) {
            return $user->hasPermissionTo('settings.manage');
        });

        Gate::define('manage-team', function ($user) {
            return $user->hasAnyPermission(['users.create', 'users.update', 'users.delete']);
        });
    }
}