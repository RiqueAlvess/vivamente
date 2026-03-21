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
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
