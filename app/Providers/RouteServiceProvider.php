<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;

class RouteServiceProvider extends ServiceProvider
{
    public const HOME = '/home';

    public function boot(): void
    {
        Log::info('Session ID on boot: ' . session()->getId());

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        Log::info('RouteServiceProvider: Registering routes');

        $this->routes(function () {
            Log::info('RouteServiceProvider: Registering API routes with api middleware');
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Log::info('RouteServiceProvider: Registering web routes with web middleware');
            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }
}