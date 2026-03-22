<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\CollaboratorController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\SurveyController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Auth Routes (todos os usuários)
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:login');
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showForm'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showForm'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');
});

Route::post('/logout', [LoginController::class, 'logout'])
    ->name('logout')
    ->middleware('auth');

/*
|--------------------------------------------------------------------------
| Survey Routes (públicas — respondentes não autenticados)
|--------------------------------------------------------------------------
*/

Route::get('/pesquisa/{token}', [SurveyController::class, 'show'])->name('survey.show');
Route::post('/pesquisa/{token}/consent', [SurveyController::class, 'consent'])->name('survey.consent');
Route::post('/pesquisa/{token}/submit', [SurveyController::class, 'submit'])->name('survey.submit');

/*
|--------------------------------------------------------------------------
| Admin Routes (global_admin)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'admin.auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::resource('companies', CompanyController::class)->names([
        'index'   => 'companies.index',
        'create'  => 'companies.create',
        'store'   => 'companies.store',
        'show'    => 'companies.show',
        'edit'    => 'companies.edit',
        'update'  => 'companies.update',
        'destroy' => 'companies.destroy',
    ]);

    Route::resource('users', AdminUserController::class)->names([
        'index'   => 'users.index',
        'create'  => 'users.create',
        'store'   => 'users.store',
        'show'    => 'users.show',
        'edit'    => 'users.edit',
        'update'  => 'users.update',
        'destroy' => 'users.destroy',
    ]);
    Route::post('/users/{user}/toggle-block', [AdminUserController::class, 'toggleBlock'])
        ->name('users.toggle-block');
});

/*
|--------------------------------------------------------------------------
| Company User Routes (rh / leader)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'company.auth'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Acessíveis somente por rh
    Route::middleware('role:rh')->group(function () {
        // Colaboradores
        Route::resource('collaborators', CollaboratorController::class)->names([
            'index'   => 'collaborators.index',
            'create'  => 'collaborators.create',
            'store'   => 'collaborators.store',
            'show'    => 'collaborators.show',
            'edit'    => 'collaborators.edit',
            'update'  => 'collaborators.update',
            'destroy' => 'collaborators.destroy',
        ]);

        // Importação CSV
        Route::get('/imports', [ImportController::class, 'index'])->name('imports.index');
        Route::post('/imports', [ImportController::class, 'store'])->name('imports.store');
        Route::get('/imports/{importJob}', [ImportController::class, 'show'])->name('imports.show');

        // Campanhas
        Route::resource('campaigns', CampaignController::class)->names([
            'index'   => 'campaigns.index',
            'create'  => 'campaigns.create',
            'store'   => 'campaigns.store',
            'show'    => 'campaigns.show',
            'edit'    => 'campaigns.edit',
            'update'  => 'campaigns.update',
            'destroy' => 'campaigns.destroy',
        ]);
        Route::post('/campaigns/{campaign}/activate', [CampaignController::class, 'activate'])
            ->name('campaigns.activate');
        Route::post('/campaigns/{campaign}/close', [CampaignController::class, 'close'])
            ->name('campaigns.close');
        Route::post('/campaigns/{campaign}/send-invites', [CampaignController::class, 'sendInvites'])
            ->name('campaigns.send-invites');

        // Gestão de usuários da empresa
        Route::resource('users', UserController::class)->names([
            'index'   => 'users.index',
            'create'  => 'users.create',
            'store'   => 'users.store',
            'show'    => 'users.show',
            'edit'    => 'users.edit',
            'update'  => 'users.update',
            'destroy' => 'users.destroy',
        ]);
        Route::post('/users/{user}/toggle-block', [UserController::class, 'toggleBlock'])
            ->name('users.toggle-block');

        // API auxiliar
        Route::get('/api/hierarchies', [UserController::class, 'getHierarchies'])
            ->name('api.hierarchies');
    });
});
