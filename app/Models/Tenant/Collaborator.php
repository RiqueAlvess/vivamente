<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Collaborator extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'unidade',
        'setor',
        'cargo',
        'genero',
        'faixa_etaria',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function surveyInvites(): HasMany
    {
        return $this->hasMany(SurveyInvite::class);
    }
}
