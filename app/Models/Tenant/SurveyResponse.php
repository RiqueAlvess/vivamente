<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurveyResponse extends Model
{
    protected $fillable = [
        'survey_invite_id',
        'campaign_id',
        'genero',
        'faixa_etaria',
        'q1', 'q2', 'q3', 'q4', 'q5', 'q6', 'q7', 'q8', 'q9', 'q10',
        'q11', 'q12', 'q13', 'q14', 'q15', 'q16', 'q17', 'q18', 'q19', 'q20',
        'q21', 'q22', 'q23', 'q24', 'q25', 'q26', 'q27', 'q28', 'q29', 'q30',
        'q31', 'q32', 'q33', 'q34', 'q35',
        'score_demandas',
        'score_controle',
        'score_apoio_chefia',
        'score_apoio_colegas',
        'score_relacionamentos',
        'score_cargo_funcao',
        'score_comunicacao_mudancas',
        'igrp',
        'risco_classificacao',
        'consent_given',
    ];

    protected function casts(): array
    {
        return [
            'consent_given' => 'boolean',
        ];
    }

    public function surveyInvite(): BelongsTo
    {
        return $this->belongsTo(SurveyInvite::class);
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /**
     * Calculate and store HSE-IT scores.
     */
    public function calculateScores(): void
    {
        // Dimensões negativas: score alto = risco alto
        $demandas = $this->average(['q1', 'q2', 'q3', 'q4', 'q5', 'q6', 'q7', 'q8']);
        $relacionamentos = $this->average(['q25', 'q26', 'q27', 'q28']);

        // Dimensões positivas: score baixo = risco alto
        $controle = $this->average(['q9', 'q10', 'q11', 'q12', 'q13', 'q14']);
        $apoioChefia = $this->average(['q15', 'q16', 'q17', 'q18', 'q19', 'q20']);
        $apoioColegas = $this->average(['q21', 'q22', 'q23', 'q24']);
        $cargoFuncao = $this->average(['q29', 'q30', 'q31', 'q32', 'q33']);
        $comunicacaoMudancas = $this->average(['q34', 'q35']);

        $this->score_demandas = $demandas;
        $this->score_controle = $controle;
        $this->score_apoio_chefia = $apoioChefia;
        $this->score_apoio_colegas = $apoioColegas;
        $this->score_relacionamentos = $relacionamentos;
        $this->score_cargo_funcao = $cargoFuncao;
        $this->score_comunicacao_mudancas = $comunicacaoMudancas;

        // Risco normalizado por dimensão (0.0 a 1.0)
        $riscos = [
            'demandas' => $demandas / 4,                       // negativa
            'controle' => (4 - $controle) / 4,                 // positiva
            'apoio_chefia' => (4 - $apoioChefia) / 4,          // positiva
            'apoio_colegas' => (4 - $apoioColegas) / 4,        // positiva
            'relacionamentos' => $relacionamentos / 4,          // negativa
            'cargo_funcao' => (4 - $cargoFuncao) / 4,          // positiva
            'comunicacao_mudancas' => (4 - $comunicacaoMudancas) / 4, // positiva
        ];

        $igrp = (array_sum($riscos) / count($riscos)) * 100;
        $this->igrp = round($igrp, 2);

        $this->risco_classificacao = match (true) {
            $igrp < 33 => 'baixo',
            $igrp <= 66 => 'moderado',
            default => 'alto',
        };
    }

    private function average(array $questions): float
    {
        $values = array_filter(
            array_map(fn($q) => $this->$q, $questions),
            fn($v) => $v !== null
        );

        if (empty($values)) {
            return 0.0;
        }

        return array_sum($values) / count($values);
    }
}
