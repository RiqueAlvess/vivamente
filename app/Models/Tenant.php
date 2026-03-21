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
     *
     * UUIDs gerados pelo Stancl\Tenancy\UUIDGenerator contêm hífens (ex: 550e8400-e29b-41d4-...).
     * Nomes de schema PostgreSQL com hífens precisam de aspas duplas em todo SQL gerado.
     * Para garantir compatibilidade sem aspas, removemos os hífens do UUID.
     * Resultado: "tenant_550e8400e29b41d4a716446655440000" — válido em qualquer contexto SQL.
     */
    public function getDatabaseName(): string
    {
        return 'tenant_' . str_replace('-', '', $this->id);
    }
}
