<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\SurveyInvite;
use App\Models\Tenant\SurveyResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SurveyController extends Controller
{
    public function show(string $token): Response|RedirectResponse
    {
        $invite = SurveyInvite::with(['campaign', 'collaborator'])
            ->where('token', $token)
            ->first();

        if (! $invite || ! $invite->campaign || ! $invite->campaign->isActive()) {
            return Inertia::render('Tenant/Survey/Invalid', [
                'reason' => 'link_invalido',
            ]);
        }

        if ($invite->isAnswered()) {
            return Inertia::render('Tenant/Survey/Invalid', [
                'reason' => 'ja_respondido',
            ]);
        }

        return Inertia::render('Tenant/Survey/Consent', [
            'token' => $token,
            'campaign' => $invite->campaign->only(['id', 'name']),
        ]);
    }

    public function consent(Request $request, string $token): Response|RedirectResponse
    {
        $request->validate([
            'accepted' => 'required|accepted',
        ]);

        $invite = SurveyInvite::where('token', $token)->first();

        if (! $invite || $invite->isAnswered() || ! $invite->campaign || ! $invite->campaign->isActive()) {
            return redirect()->back();
        }

        return Inertia::render('Tenant/Survey/Questionnaire', [
            'token' => $token,
            'collaborator' => $invite->collaborator->only(['genero', 'faixa_etaria']),
            'questions' => $this->getQuestions(),
        ]);
    }

    public function submit(Request $request, string $token): Response|RedirectResponse
    {
        $invite = SurveyInvite::with('campaign')->where('token', $token)->first();

        if (! $invite || $invite->isAnswered() || ! $invite->campaign || ! $invite->campaign->isActive()) {
            return Inertia::render('Tenant/Survey/Invalid', [
                'reason' => 'link_invalido',
            ]);
        }

        $request->validate([
            'consent_given' => 'required|accepted',
            'genero' => 'nullable|string|max:50',
            'faixa_etaria' => 'nullable|string|max:50',
            'q1' => 'required|integer|between:0,4',
            'q2' => 'required|integer|between:0,4',
            'q3' => 'required|integer|between:0,4',
            'q4' => 'required|integer|between:0,4',
            'q5' => 'required|integer|between:0,4',
            'q6' => 'required|integer|between:0,4',
            'q7' => 'required|integer|between:0,4',
            'q8' => 'required|integer|between:0,4',
            'q9' => 'required|integer|between:0,4',
            'q10' => 'required|integer|between:0,4',
            'q11' => 'required|integer|between:0,4',
            'q12' => 'required|integer|between:0,4',
            'q13' => 'required|integer|between:0,4',
            'q14' => 'required|integer|between:0,4',
            'q15' => 'required|integer|between:0,4',
            'q16' => 'required|integer|between:0,4',
            'q17' => 'required|integer|between:0,4',
            'q18' => 'required|integer|between:0,4',
            'q19' => 'required|integer|between:0,4',
            'q20' => 'required|integer|between:0,4',
            'q21' => 'required|integer|between:0,4',
            'q22' => 'required|integer|between:0,4',
            'q23' => 'required|integer|between:0,4',
            'q24' => 'required|integer|between:0,4',
            'q25' => 'required|integer|between:0,4',
            'q26' => 'required|integer|between:0,4',
            'q27' => 'required|integer|between:0,4',
            'q28' => 'required|integer|between:0,4',
            'q29' => 'required|integer|between:0,4',
            'q30' => 'required|integer|between:0,4',
            'q31' => 'required|integer|between:0,4',
            'q32' => 'required|integer|between:0,4',
            'q33' => 'required|integer|between:0,4',
            'q34' => 'required|integer|between:0,4',
            'q35' => 'required|integer|between:0,4',
        ]);

        $questionData = array_merge(
            ['consent_given' => true, 'genero' => $request->genero, 'faixa_etaria' => $request->faixa_etaria],
            collect(range(1, 35))->mapWithKeys(fn($i) => ["q{$i}" => $request->input("q{$i}")])->toArray()
        );

        $response = new SurveyResponse(array_merge($questionData, [
            'survey_invite_id' => $invite->id,
            'campaign_id' => $invite->campaign_id,
        ]));

        $response->calculateScores();
        $response->save();

        $invite->update([
            'status' => 'respondido',
            'responded_at' => now(),
        ]);

        return Inertia::render('Tenant/Survey/Done');
    }

    private function getQuestions(): array
    {
        return [
            // Demandas (negativa)
            ['id' => 1, 'dimension' => 'demandas', 'text' => 'Tenho que trabalhar em ritmo muito acelerado.'],
            ['id' => 2, 'dimension' => 'demandas', 'text' => 'Tenho prazos impossíveis de cumprir.'],
            ['id' => 3, 'dimension' => 'demandas', 'text' => 'Tenho que trabalhar horas extras ou nos fins de semana.'],
            ['id' => 4, 'dimension' => 'demandas', 'text' => 'Diferentes grupos no trabalho me exigem coisas difíceis de conciliar.'],
            ['id' => 5, 'dimension' => 'demandas', 'text' => 'Sinto que tenho mais trabalho do que consigo realizar.'],
            ['id' => 6, 'dimension' => 'demandas', 'text' => 'Tenho que negligenciar algumas tarefas porque tenho muito trabalho.'],
            ['id' => 7, 'dimension' => 'demandas', 'text' => 'Fico sob pressão excessiva no trabalho.'],
            ['id' => 8, 'dimension' => 'demandas', 'text' => 'Não consigo realizar pausas suficientes durante a jornada de trabalho.'],
            // Controle (positiva)
            ['id' => 9,  'dimension' => 'controle', 'text' => 'Posso decidir quando fazer pausas no trabalho.'],
            ['id' => 10, 'dimension' => 'controle', 'text' => 'Tenho liberdade para escolher como realizar o meu trabalho.'],
            ['id' => 11, 'dimension' => 'controle', 'text' => 'Posso influenciar no ritmo do meu trabalho.'],
            ['id' => 12, 'dimension' => 'controle', 'text' => 'Tenho autonomia suficiente para realizar bem o meu trabalho.'],
            ['id' => 13, 'dimension' => 'controle', 'text' => 'Consigo me desenvolver com as tarefas que realizo no trabalho.'],
            ['id' => 14, 'dimension' => 'controle', 'text' => 'Meu trabalho me dá a oportunidade de usar minhas habilidades.'],
            // Apoio da Chefia (positiva)
            ['id' => 15, 'dimension' => 'apoio_chefia', 'text' => 'Meu superior imediato me dá o apoio de que preciso.'],
            ['id' => 16, 'dimension' => 'apoio_chefia', 'text' => 'Meu superior imediato está disposto a ouvir os meus problemas de trabalho.'],
            ['id' => 17, 'dimension' => 'apoio_chefia', 'text' => 'Meu superior imediato me elogia quando faço um bom trabalho.'],
            ['id' => 18, 'dimension' => 'apoio_chefia', 'text' => 'Meu superior imediato age de forma positiva com relação ao bem-estar dos colaboradores.'],
            ['id' => 19, 'dimension' => 'apoio_chefia', 'text' => 'Meu superior imediato responde às minhas solicitações de flexibilidade quando necessário.'],
            ['id' => 20, 'dimension' => 'apoio_chefia', 'text' => 'Meu superior imediato dá atenção adequada ao que digo.'],
            // Apoio dos Colegas (positiva)
            ['id' => 21, 'dimension' => 'apoio_colegas', 'text' => 'Se precisar, meus colegas são capazes de me ajudar em situações difíceis no trabalho.'],
            ['id' => 22, 'dimension' => 'apoio_colegas', 'text' => 'Recebo apoio dos meus colegas quando necessário.'],
            ['id' => 23, 'dimension' => 'apoio_colegas', 'text' => 'Meus colegas estão dispostos a ouvir meus problemas de trabalho.'],
            ['id' => 24, 'dimension' => 'apoio_colegas', 'text' => 'Meus colegas me ajudam quando há muitas tarefas a fazer.'],
            // Relacionamentos (negativa)
            ['id' => 25, 'dimension' => 'relacionamentos', 'text' => 'Sou submetido a comportamentos agressivos no trabalho, como ser intimidado ou assediado.'],
            ['id' => 26, 'dimension' => 'relacionamentos', 'text' => 'Há tensão e conflitos no meu ambiente de trabalho.'],
            ['id' => 27, 'dimension' => 'relacionamentos', 'text' => 'Sofro assédio moral de meus superiores ou colegas.'],
            ['id' => 28, 'dimension' => 'relacionamentos', 'text' => 'Os relacionamentos no trabalho são hostis ou desrespeitosos.'],
            // Cargo/Função (positiva)
            ['id' => 29, 'dimension' => 'cargo_funcao', 'text' => 'Sei claramente quais são as responsabilidades do meu cargo.'],
            ['id' => 30, 'dimension' => 'cargo_funcao', 'text' => 'Tenho clareza sobre o que se espera de mim no trabalho.'],
            ['id' => 31, 'dimension' => 'cargo_funcao', 'text' => 'Sei quais são os objetivos e metas do meu setor.'],
            ['id' => 32, 'dimension' => 'cargo_funcao', 'text' => 'Compreendo como o meu trabalho contribui para os objetivos da organização.'],
            ['id' => 33, 'dimension' => 'cargo_funcao', 'text' => 'Tenho tarefas claras e bem definidas.'],
            // Comunicação e Mudanças (positiva)
            ['id' => 34, 'dimension' => 'comunicacao_mudancas', 'text' => 'Sou informado previamente quando há mudanças importantes no trabalho.'],
            ['id' => 35, 'dimension' => 'comunicacao_mudancas', 'text' => 'Recebo informações adequadas sobre como as mudanças me afetam.'],
        ];
    }
}
