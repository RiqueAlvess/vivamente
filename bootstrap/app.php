<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            App\Http\Middleware\HandleInertiaRequests::class,
            Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        // Ensure PreventAccessFromCentralDomains runs before Authenticate so
        // central-domain requests to tenant-only routes get 403 (not a login redirect).
        $middleware->priority([
            \Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests::class,
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class,
            \Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests::class,
            \Illuminate\Routing\Middleware\ThrottleRequests::class,
            \Illuminate\Routing\Middleware\ThrottleRequestsWithRedis::class,
            \Illuminate\Contracts\Session\Middleware\AuthenticatesSessions::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \Illuminate\Auth\Middleware\Authorize::class,
        ]);

        $middleware->alias([
            'role' => App\Http\Middleware\CheckRole::class,
            'tenant.auth' => App\Http\Middleware\EnsureTenantUser::class,
            'central.auth' => App\Http\Middleware\EnsureCentralUser::class,
        ]);

        $middleware->redirectGuestsTo(function ($request) {
            $centralDomains = config('tenancy.central_domains', ['localhost']);
            if (in_array($request->getHost(), $centralDomains)) {
                return route('central.login');
            }
            return route('tenant.login');
        });

        $middleware->redirectUsersTo(function ($request) {
            $centralDomains = config('tenancy.central_domains', ['localhost']);
            if (in_array($request->getHost(), $centralDomains)) {
                return route('central.dashboard');
            }
            return route('tenant.dashboard');
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
