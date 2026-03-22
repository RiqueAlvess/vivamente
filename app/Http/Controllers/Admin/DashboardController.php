<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $totalCompanies = Company::count();
        $activeCompanies = Company::where('is_active', true)->count();
        $totalUsers = User::whereNotNull('company_id')->count();

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'total_companies' => $totalCompanies,
                'active_companies' => $activeCompanies,
                'total_users' => $totalUsers,
            ],
        ]);
    }
}
