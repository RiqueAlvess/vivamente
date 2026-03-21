<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Jobs\SendCampaignInvites;
use App\Models\Tenant\Campaign;
use App\Models\Tenant\Collaborator;
use App\Models\Tenant\SurveyInvite;
use App\Services\HseItAnalyticsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CampaignController extends Controller
{
    public function __construct(
        private readonly HseItAnalyticsService $analytics
    ) {}

    public function index(): Response
    {
        $campaigns = Campaign::withCount([
            'surveyInvites',
            'surveyInvites as responded_count' => fn($q) => $q->where('status', 'respondido'),
        ])->latest()->paginate(15);

        return Inertia::render('Tenant/Campaigns/Index', [
            'campaigns' => $campaigns,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Tenant/Campaigns/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
        ]);

        $campaign = Campaign::create([
            'name' => $request->name,
            'description' => $request->description,
            'status' => 'rascunho',
            'starts_at' => $request->starts_at,
            'ends_at' => $request->ends_at,
        ]);

        return redirect()->route('tenant.campaigns.show', $campaign)
            ->with('success', 'Campanha criada com sucesso.');
    }

    public function show(Campaign $campaign): Response
    {
        $campaign->loadCount([
            'surveyInvites',
            'surveyInvites as responded_count' => fn($q) => $q->where('status', 'respondido'),
        ]);

        $chartData = $campaign->status !== 'rascunho'
            ? $this->analytics->getChartData($campaign)
            : null;

        $stats = $this->analytics->getCompanyStats($campaign);

        return Inertia::render('Tenant/Campaigns/Show', [
            'campaign' => $campaign,
            'stats' => $stats,
            'chartData' => $chartData,
        ]);
    }

    public function edit(Campaign $campaign): Response
    {
        return Inertia::render('Tenant/Campaigns/Edit', [
            'campaign' => $campaign,
        ]);
    }

    public function update(Request $request, Campaign $campaign): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date',
        ]);

        $campaign->update($request->only(['name', 'description', 'starts_at', 'ends_at']));

        return redirect()->route('tenant.campaigns.show', $campaign)
            ->with('success', 'Campanha atualizada com sucesso.');
    }

    public function destroy(Campaign $campaign): RedirectResponse
    {
        $campaign->delete();

        return redirect()->route('tenant.campaigns.index')
            ->with('success', 'Campanha removida com sucesso.');
    }

    public function activate(Campaign $campaign): RedirectResponse
    {
        if (! $campaign->isDraft()) {
            return back()->withErrors(['status' => 'Apenas campanhas em rascunho podem ser ativadas.']);
        }

        $campaign->update(['status' => 'ativa', 'starts_at' => $campaign->starts_at ?? now()]);

        return back()->with('success', 'Campanha ativada com sucesso.');
    }

    public function close(Campaign $campaign): RedirectResponse
    {
        if (! $campaign->isActive()) {
            return back()->withErrors(['status' => 'Apenas campanhas ativas podem ser encerradas.']);
        }

        $campaign->update(['status' => 'encerrada', 'ends_at' => $campaign->ends_at ?? now()]);

        return back()->with('success', 'Campanha encerrada com sucesso.');
    }

    public function sendInvites(Campaign $campaign): RedirectResponse
    {
        if (! $campaign->isActive()) {
            return back()->withErrors(['status' => 'A campanha precisa estar ativa para enviar convites.']);
        }

        // Get collaborators without invite for this campaign
        $existingCollaboratorIds = $campaign->surveyInvites()->pluck('collaborator_id');
        $collaborators = Collaborator::whereNotIn('id', $existingCollaboratorIds)
            ->where('is_active', true)
            ->get();

        if ($collaborators->isEmpty()) {
            return back()->with('info', 'Todos os colaboradores já receberam convites.');
        }

        // Create invites
        $invites = $collaborators->map(function ($collaborator) use ($campaign) {
            return SurveyInvite::firstOrCreate(
                ['campaign_id' => $campaign->id, 'collaborator_id' => $collaborator->id],
                ['token' => SurveyInvite::generateToken(), 'status' => 'pendente']
            );
        });

        // Dispatch job to send emails
        SendCampaignInvites::dispatch($campaign->id, $invites->pluck('id')->toArray(), tenant('id'));

        return back()->with('success', "Envio de {$collaborators->count()} convites iniciado em background.");
    }
}
