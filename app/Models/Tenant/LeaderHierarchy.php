<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaderHierarchy extends Model
{
    protected $table = 'leader_hierarchies';

    protected $fillable = [
        'user_id',
        'unidade',
        'setor',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
