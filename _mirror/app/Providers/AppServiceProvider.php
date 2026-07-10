<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

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
        // Gate para superadmin
        Gate::define('superadmin', function ($user) {
            return $user->isSuperAdmin();
        });

        // Gate para admin (incluye superadmin)
        Gate::define('admin', function ($user) {
            return $user->isAdmin();
        });
    }
}
