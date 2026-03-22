<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
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

        return Inertia::render('Dashboard/Rh', [
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

        return Inertia::render('Dashboard/Leader', [
            'campaigns' => $campaigns,
            'hierarchies' => $hierarchies,
            'stats' => $stats,
            'chartData' => $chartData,
            'selectedCampaign' => $latestCampaign,
        ]);
    }
}
