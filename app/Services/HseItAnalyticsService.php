<?php

namespace App\Services;

use App\Models\Tenant\Campaign;
use App\Models\Tenant\SurveyInvite;
use App\Models\Tenant\SurveyResponse;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class HseItAnalyticsService
{
    private const DIMENSIONS = [
        'demandas' => ['label' => 'Demandas', 'type' => 'negative'],
        'controle' => ['label' => 'Controle', 'type' => 'positive'],
        'apoio_chefia' => ['label' => 'Apoio da Chefia', 'type' => 'positive'],
        'apoio_colegas' => ['label' => 'Apoio dos Colegas', 'type' => 'positive'],
        'relacionamentos' => ['label' => 'Relacionamentos', 'type' => 'negative'],
        'cargo_funcao' => ['label' => 'Cargo/Função', 'type' => 'positive'],
        'comunicacao_mudancas' => ['label' => 'Comunicação e Mudanças', 'type' => 'positive'],
    ];

    public function getCompanyStats(?Campaign $campaign): array
    {
        if (! $campaign) {
            return $this->emptyStats();
        }

        $totalInvites = $campaign->surveyInvites()->count();
        $respondedCount = $campaign->surveyInvites()->where('status', 'respondido')->count();
        $adesao = $totalInvites > 0 ? round(($respondedCount / $totalInvites) * 100, 1) : 0;

        $responses = SurveyResponse::where('campaign_id', $campaign->id)->get();
        $igrp = $responses->avg('igrp') ?? 0;
        $riscoAlto = $responses->where('risco_classificacao', 'alto')->count();
        $percRiscoAlto = $responses->count() > 0
            ? round(($riscoAlto / $responses->count()) * 100, 1)
            : 0;

        return [
            'total_invites' => $totalInvites,
            'responded' => $respondedCount,
            'adesao' => $adesao,
            'igrp' => round($igrp, 1),
            'perc_risco_alto' => $percRiscoAlto,
            'risco_classificacao' => $this->classifyIgrp($igrp),
        ];
    }

    public function getLeaderStats(?Campaign $campaign, Collection $hierarchies): array
    {
        if (! $campaign || $hierarchies->isEmpty()) {
            return $this->emptyStats();
        }

        $inviteIds = $this->getInviteIdsForHierarchies($campaign, $hierarchies);

        $totalInvites = $inviteIds->count();
        $respondedCount = SurveyInvite::whereIn('id', $inviteIds)->where('status', 'respondido')->count();
        $adesao = $totalInvites > 0 ? round(($respondedCount / $totalInvites) * 100, 1) : 0;

        $responses = SurveyResponse::whereIn('survey_invite_id', $inviteIds)->get();
        $igrp = $responses->avg('igrp') ?? 0;
        $riscoAlto = $responses->where('risco_classificacao', 'alto')->count();
        $percRiscoAlto = $responses->count() > 0
            ? round(($riscoAlto / $responses->count()) * 100, 1)
            : 0;

        return [
            'total_invites' => $totalInvites,
            'responded' => $respondedCount,
            'adesao' => $adesao,
            'igrp' => round($igrp, 1),
            'perc_risco_alto' => $percRiscoAlto,
            'risco_classificacao' => $this->classifyIgrp($igrp),
        ];
    }

    public function getChartData(Campaign $campaign): array
    {
        $responses = SurveyResponse::where('campaign_id', $campaign->id)->get();

        return [
            'radar' => $this->buildRadarData($responses),
            'doughnut' => $this->buildDoughnutData($responses),
            'bar' => $this->buildBarData($campaign),
        ];
    }

    public function getLeaderChartData(Campaign $campaign, Collection $hierarchies): array
    {
        $inviteIds = $this->getInviteIdsForHierarchies($campaign, $hierarchies);
        $responses = SurveyResponse::whereIn('survey_invite_id', $inviteIds)->get();

        return [
            'radar' => $this->buildRadarData($responses),
            'doughnut' => $this->buildDoughnutData($responses),
            'bar' => $this->buildBarData($campaign, $inviteIds),
        ];
    }

    private function buildRadarData(Collection $responses): array
    {
        $labels = [];
        $data = [];

        foreach (self::DIMENSIONS as $key => $info) {
            $labels[] = $info['label'];
            $score = $responses->avg("score_{$key}") ?? 0;

            // Normalize to risk (0-100%)
            $risk = $info['type'] === 'negative'
                ? ($score / 4) * 100
                : ((4 - $score) / 4) * 100;

            $data[] = round($risk, 1);
        }

        return ['labels' => $labels, 'data' => $data];
    }

    private function buildDoughnutData(Collection $responses): array
    {
        $baixo = $responses->where('risco_classificacao', 'baixo')->count();
        $moderado = $responses->where('risco_classificacao', 'moderado')->count();
        $alto = $responses->where('risco_classificacao', 'alto')->count();

        return [
            'labels' => ['Risco Baixo', 'Risco Moderado', 'Risco Alto'],
            'data' => [$baixo, $moderado, $alto],
            'colors' => ['#22c55e', '#f59e0b', '#ef4444'],
        ];
    }

    private function buildBarData(Campaign $campaign, $inviteIds = null): array
    {
        $query = SurveyResponse::where('campaign_id', $campaign->id);

        if ($inviteIds) {
            $query->whereIn('survey_invite_id', $inviteIds);
        }

        $bySetor = $query->join('survey_invites', 'survey_responses.survey_invite_id', '=', 'survey_invites.id')
            ->join('collaborators', 'survey_invites.collaborator_id', '=', 'collaborators.id')
            ->select('collaborators.setor', DB::raw('AVG(igrp) as avg_igrp'), DB::raw('COUNT(*) as total'))
            ->groupBy('collaborators.setor')
            ->orderByDesc('avg_igrp')
            ->get();

        return [
            'labels' => $bySetor->pluck('setor')->toArray(),
            'data' => $bySetor->map(fn($r) => round($r->avg_igrp, 1))->toArray(),
        ];
    }

    private function getInviteIdsForHierarchies(Campaign $campaign, Collection $hierarchies)
    {
        return SurveyInvite::where('campaign_id', $campaign->id)
            ->join('collaborators', 'survey_invites.collaborator_id', '=', 'collaborators.id')
            ->where(function ($query) use ($hierarchies) {
                foreach ($hierarchies as $h) {
                    $query->orWhere(function ($q) use ($h) {
                        $q->where('collaborators.unidade', $h->unidade)
                          ->where('collaborators.setor', $h->setor);
                    });
                }
            })
            ->pluck('survey_invites.id');
    }

    private function classifyIgrp(float $igrp): string
    {
        return match (true) {
            $igrp < 33 => 'baixo',
            $igrp <= 66 => 'moderado',
            default => 'alto',
        };
    }

    private function emptyStats(): array
    {
        return [
            'total_invites' => 0,
            'responded' => 0,
            'adesao' => 0,
            'igrp' => 0,
            'perc_risco_alto' => 0,
            'risco_classificacao' => 'baixo',
        ];
    }
}
