<?php

use App\Http\Controllers\Central\Auth\LoginController as CentralLoginController;
use App\Http\Controllers\Central\Auth\ForgotPasswordController as CentralForgotPasswordController;
use App\Http\Controllers\Central\Auth\ResetPasswordController as CentralResetPasswordController;
use App\Http\Controllers\Central\DashboardController as CentralDashboardController;
use App\Http\Controllers\Central\TenantController;
use App\Http\Controllers\Central\UserController as CentralUserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Central Routes (localhost)
|--------------------------------------------------------------------------
*/

foreach (config('tenancy.central_domains') as $domain) {
    Route::domain($domain)->group(function () {
        // Auth routes
        Route::middleware('guest')->group(function () {
            Route::get('/login', [CentralLoginController::class, 'showLoginForm'])->name('central.login');
            Route::post('/login', [CentralLoginController::class, 'login'])->middleware('throttle:login');
            Route::get('/forgot-password', [CentralForgotPasswordController::class, 'showForm'])->name('central.password.request');
            Route::post('/forgot-password', [CentralForgotPasswordController::class, 'sendResetLink'])->name('central.password.email');
            Route::get('/reset-password/{token}', [CentralResetPasswordController::class, 'showForm'])->name('central.password.reset');
            Route::post('/reset-password', [CentralResetPasswordController::class, 'reset'])->name('central.password.update');
        });

        Route::post('/logout', [CentralLoginController::class, 'logout'])
            ->name('central.logout')
            ->middleware('auth');

        // Protected admin routes
        Route::middleware(['auth', 'central.auth'])->group(function () {
            Route::get('/', [CentralDashboardController::class, 'index'])->name('central.dashboard');

            // Tenants
            Route::resource('tenants', TenantController::class)->names([
                'index'   => 'central.tenants.index',
                'create'  => 'central.tenants.create',
                'store'   => 'central.tenants.store',
                'show'    => 'central.tenants.show',
                'edit'    => 'central.tenants.edit',
                'update'  => 'central.tenants.update',
                'destroy' => 'central.tenants.destroy',
            ]);

            // Users
            Route::resource('users', CentralUserController::class)
                ->only(['index', 'create', 'store'])
                ->names([
                    'index'  => 'central.users.index',
                    'create' => 'central.users.create',
                    'store'  => 'central.users.store',
                ]);
            Route::get('/users/{tenantId}/{userId}', [CentralUserController::class, 'show'])
                ->name('central.users.show');
            Route::get('/users/{tenantId}/{userId}/edit', [CentralUserController::class, 'edit'])
                ->name('central.users.edit');
            Route::put('/users/{tenantId}/{userId}', [CentralUserController::class, 'update'])
                ->name('central.users.update');
            Route::patch('/users/{tenantId}/{userId}', [CentralUserController::class, 'update']);
            Route::delete('/users/{tenantId}/{userId}', [CentralUserController::class, 'destroy'])
                ->name('central.users.destroy');
            Route::post('/users/{tenantId}/{userId}/toggle-block', [CentralUserController::class, 'toggleBlock'])
                ->name('central.users.toggle-block');
        });
    });
}
