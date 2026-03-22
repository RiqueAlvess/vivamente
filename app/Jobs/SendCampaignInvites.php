<?php

namespace App\Jobs;

use App\Mail\SurveyInviteMail;
use App\Models\Company;
use App\Models\SurveyInvite;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendCampaignInvites implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 300;

    public function __construct(
        private readonly array $inviteIds,
        private readonly int $companyId,
    ) {}

    public function handle(): void
    {
        $invites = SurveyInvite::withoutGlobalScopes()
            ->with(['collaborator', 'campaign'])
            ->whereIn('id', $this->inviteIds)
            ->where('company_id', $this->companyId)
            ->get();

        $surveyBaseUrl = config('app.url');

        foreach ($invites as $invite) {
            $surveyUrl = "{$surveyBaseUrl}/pesquisa/{$invite->token}";

            try {
                Mail::to($invite->collaborator->email)
                    ->send(new SurveyInviteMail($invite, $surveyUrl));

                $invite->update(['status' => 'enviado', 'sent_at' => now()]);
            } catch (\Exception $e) {
                \Log::error("Failed to send invite {$invite->id}: " . $e->getMessage());
            }
        }
    }
}
