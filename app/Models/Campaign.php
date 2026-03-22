<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Campaign extends Model
{
    use SoftDeletes, BelongsToCompany;

    protected $fillable = [
        'company_id',
        'name',
        'description',
        'status',
        'starts_at',
        'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function surveyInvites(): HasMany
    {
        return $this->hasMany(SurveyInvite::class);
    }

    public function surveyResponses(): HasMany
    {
        return $this->hasMany(SurveyResponse::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'ativa';
    }

    public function isDraft(): bool
    {
        return $this->status === 'rascunho';
    }

    public function isClosed(): bool
    {
        return $this->status === 'encerrada';
    }
}
