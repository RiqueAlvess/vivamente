<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaderHierarchy extends Model
{
    use BelongsToCompany;

    protected $table = 'leader_hierarchies';

    protected $fillable = [
        'company_id',
        'user_id',
        'unidade',
        'setor',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
