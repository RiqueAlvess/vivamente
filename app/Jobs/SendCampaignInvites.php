<?php

namespace App\Jobs;

use App\Mail\SurveyInviteMail;
use App\Models\Tenant;
use App\Models\Tenant\SurveyInvite;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Stancl\Tenancy\Concerns\UsableWithTenancy;

class SendCampaignInvites implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, UsableWithTenancy;

    public int $tries = 3;
    public int $timeout = 300;

    public function __construct(
        private readonly int $campaignId,
        private readonly array $inviteIds,
        private readonly string $tenantId,
    ) {}

    public function handle(): void
    {
        $tenant = Tenant::find($this->tenantId);
        tenancy()->initialize($tenant);

        $tenantDomain = $tenant->domains()->first()?->domain ?? config('tenancy.central_domains')[0];

        $invites = SurveyInvite::with(['collaborator', 'campaign'])
            ->whereIn('id', $this->inviteIds)
            ->get();

        foreach ($invites as $invite) {
            $surveyUrl = "http://{$tenantDomain}/pesquisa/{$invite->token}";

            try {
                Mail::to($invite->collaborator->email)
                    ->send(new SurveyInviteMail($invite, $surveyUrl));

                $invite->update(['status' => 'enviado', 'sent_at' => now()]);
            } catch (\Exception $e) {
                \Log::error("Failed to send invite {$invite->id}: " . $e->getMessage());
            }
        }

        tenancy()->end();
    }
}
