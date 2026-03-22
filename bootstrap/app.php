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
            'role'         => App\Http\Middleware\CheckRole::class,
            'company.auth' => App\Http\Middleware\EnsureCompanyUser::class,
            'admin.auth'   => App\Http\Middleware\EnsureAdminUser::class,
        ]);

        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(fn ($request) => $request->user()?->isGlobalAdmin()
            ? route('admin.dashboard')
            : route('dashboard')
        );
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
