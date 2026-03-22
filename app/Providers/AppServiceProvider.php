<?php

namespace App\Providers;

use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            \App\Services\HseItAnalyticsService::class,
            \App\Services\HseItAnalyticsService::class,
        );
    }

    public function boot(): void
    {
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });

        // Return 403 (not the default 404) when central domain accesses tenant routes.
        PreventAccessFromCentralDomains::$abortRequest = function () {
            abort(403);
        };
    }
}
