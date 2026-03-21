<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $totalTenants = Tenant::count();
        $activeTenants = Tenant::where('is_active', true)->count();

        // Count total users across all tenants
        $totalUsers = 0;
        Tenant::all()->each(function (Tenant $tenant) use (&$totalUsers) {
            tenancy()->initialize($tenant);
            $totalUsers += \App\Models\Tenant\User::count();
            tenancy()->end();
        });

        return Inertia::render('Central/Dashboard', [
            'stats' => [
                'total_tenants' => $totalTenants,
                'active_tenants' => $activeTenants,
                'total_users' => $totalUsers,
            ],
        ]);
    }
}
