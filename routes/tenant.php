<?php

declare(strict_types=1);

use App\Http\Controllers\Tenant\Auth\LoginController as TenantLoginController;
use App\Http\Controllers\Tenant\Auth\ForgotPasswordController as TenantForgotPasswordController;
use App\Http\Controllers\Tenant\Auth\ResetPasswordController as TenantResetPasswordController;
use App\Http\Controllers\Tenant\DashboardController as TenantDashboardController;
use App\Http\Controllers\Tenant\CollaboratorController;
use App\Http\Controllers\Tenant\CampaignController;
use App\Http\Controllers\Tenant\SurveyController;
use App\Http\Controllers\Tenant\UserController as TenantUserController;
use App\Http\Controllers\Tenant\ImportController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Tenant Routes ({slug}.localhost)
|--------------------------------------------------------------------------
*/

// Auth routes
Route::middleware('guest:tenant')->group(function () {
    Route::get('/login', [TenantLoginController::class, 'showLoginForm'])->name('tenant.login');
    Route::post('/login', [TenantLoginController::class, 'login'])->middleware('throttle:login');
    Route::get('/forgot-password', [TenantForgotPasswordController::class, 'showForm'])->name('tenant.password.request');
    Route::post('/forgot-password', [TenantForgotPasswordController::class, 'sendResetLink'])->name('tenant.password.email');
    Route::get('/reset-password/{token}', [TenantResetPasswordController::class, 'showForm'])->name('tenant.password.reset');
    Route::post('/reset-password', [TenantResetPasswordController::class, 'reset'])->name('tenant.password.update');
});

Route::post('/logout', [TenantLoginController::class, 'logout'])
    ->name('tenant.logout')
    ->middleware('auth:tenant');

// Public survey route (no auth needed)
Route::get('/pesquisa/{token}', [SurveyController::class, 'show'])->name('tenant.survey.show');
Route::post('/pesquisa/{token}/consent', [SurveyController::class, 'consent'])->name('tenant.survey.consent');
Route::post('/pesquisa/{token}/submit', [SurveyController::class, 'submit'])->name('tenant.survey.submit');

// Protected tenant routes
Route::middleware(['auth:tenant', 'tenant.auth'])->group(function () {
    Route::get('/', [TenantDashboardController::class, 'index'])->name('tenant.dashboard');

    // Routes accessible by rh only
    Route::middleware('role:rh')->group(function () {
        // Collaborators
        Route::resource('collaborators', CollaboratorController::class)->names([
            'index'   => 'tenant.collaborators.index',
            'create'  => 'tenant.collaborators.create',
            'store'   => 'tenant.collaborators.store',
            'show'    => 'tenant.collaborators.show',
            'edit'    => 'tenant.collaborators.edit',
            'update'  => 'tenant.collaborators.update',
            'destroy' => 'tenant.collaborators.destroy',
        ]);

        // Import
        Route::get('/imports', [ImportController::class, 'index'])->name('tenant.imports.index');
        Route::post('/imports', [ImportController::class, 'store'])->name('tenant.imports.store');
        Route::get('/imports/{importJob}', [ImportController::class, 'show'])->name('tenant.imports.show');

        // Campaigns
        Route::resource('campaigns', CampaignController::class)->names([
            'index'   => 'tenant.campaigns.index',
            'create'  => 'tenant.campaigns.create',
            'store'   => 'tenant.campaigns.store',
            'show'    => 'tenant.campaigns.show',
            'edit'    => 'tenant.campaigns.edit',
            'update'  => 'tenant.campaigns.update',
            'destroy' => 'tenant.campaigns.destroy',
        ]);
        Route::post('/campaigns/{campaign}/activate', [CampaignController::class, 'activate'])
            ->name('tenant.campaigns.activate');
        Route::post('/campaigns/{campaign}/close', [CampaignController::class, 'close'])
            ->name('tenant.campaigns.close');
        Route::post('/campaigns/{campaign}/send-invites', [CampaignController::class, 'sendInvites'])
            ->name('tenant.campaigns.send-invites');

        // Tenant User Management
        Route::resource('users', TenantUserController::class)->names([
            'index'   => 'tenant.users.index',
            'create'  => 'tenant.users.create',
            'store'   => 'tenant.users.store',
            'show'    => 'tenant.users.show',
            'edit'    => 'tenant.users.edit',
            'update'  => 'tenant.users.update',
            'destroy' => 'tenant.users.destroy',
        ]);
        Route::post('/users/{user}/toggle-block', [TenantUserController::class, 'toggleBlock'])
            ->name('tenant.users.toggle-block');

        // Hierarchies for creating/editing leaders
        Route::get('/api/hierarchies', [TenantUserController::class, 'getHierarchies'])
            ->name('tenant.api.hierarchies');
    });
});
