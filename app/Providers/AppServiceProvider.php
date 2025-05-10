<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Add custom service registrations here if needed
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Add bootstrap code here if needed
        // Example: \Illuminate\Support\Facades\Schema::defaultStringLength(191);
    }
}