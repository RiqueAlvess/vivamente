<?php

namespace App\Models;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    protected $fillable = [
        'id',
        'name',
        'slug',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'name',
            'slug',
            'is_active',
        ];
    }

    public function getConnectionName(): string
    {
        return 'tenant';
    }

    /**
     * Get the schema name for this tenant (used by PostgreSQL schema manager).
     */
    public function getDatabaseName(): string
    {
        return 'tenant_' . $this->id;
    }
}
