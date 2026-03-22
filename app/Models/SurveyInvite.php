<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class SurveyInvite extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'campaign_id',
        'collaborator_id',
        'token',
        'status',
        'sent_at',
        'responded_at',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
            'responded_at' => 'datetime',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function collaborator(): BelongsTo
    {
        return $this->belongsTo(Collaborator::class);
    }

    public function response(): HasOne
    {
        return $this->hasOne(SurveyResponse::class);
    }

    public static function generateToken(): string
    {
        return Str::random(64);
    }

    public function isPending(): bool
    {
        return $this->status === 'pendente';
    }

    public function isAnswered(): bool
    {
        return $this->status === 'respondido';
    }
}
