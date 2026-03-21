<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Campaign;
use App\Models\Tenant\Collaborator;
use App\Models\Tenant\LeaderHierarchy;
use App\Models\Tenant\SurveyInvite;
use App\Models\Tenant\SurveyResponse;
use App\Services\HseItAnalyticsService;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private readonly HseItAnalyticsService $analytics
    ) {}

    public function index(): Response
    {
        $user = Auth::user();

        if ($user->isRh()) {
            return $this->rhDashboard();
        }

        return $this->leaderDashboard();
    }

    private function rhDashboard(): Response
    {
        $campaigns = Campaign::latest()->get();
        $latestCampaign = $campaigns->first();

        $stats = $this->analytics->getCompanyStats($latestCampaign);
        $chartData = $latestCampaign ? $this->analytics->getChartData($latestCampaign) : null;

        return Inertia::render('Tenant/Dashboard/Rh', [
            'campaigns' => $campaigns,
            'stats' => $stats,
            'chartData' => $chartData,
            'selectedCampaign' => $latestCampaign,
        ]);
    }

    private function leaderDashboard(): Response
    {
        $user = Auth::user();
        $hierarchies = $user->hierarchies()->get();

        $campaigns = Campaign::latest()->get();
        $latestCampaign = $campaigns->first();

        $stats = $this->analytics->getLeaderStats($latestCampaign, $hierarchies);
        $chartData = $latestCampaign ? $this->analytics->getLeaderChartData($latestCampaign, $hierarchies) : null;

        return Inertia::render('Tenant/Dashboard/Leader', [
            'campaigns' => $campaigns,
            'hierarchies' => $hierarchies,
            'stats' => $stats,
            'chartData' => $chartData,
            'selectedCampaign' => $latestCampaign,
        ]);
    }
}
