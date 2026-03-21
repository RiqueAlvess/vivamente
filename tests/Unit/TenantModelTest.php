<?php

namespace Tests\Unit;

use App\Models\Tenant;
use PHPUnit\Framework\TestCase;

class TenantModelTest extends TestCase
{
    /**
     * Schema names com hífens de UUID quebram SQL PostgreSQL sem aspas.
     * getDatabaseName() deve retornar string sem hífens.
     */
    public function test_database_name_has_no_hyphens(): void
    {
        $tenant = new Tenant();
        $tenant->id = '550e8400-e29b-41d4-a716-446655440000';

        $name = $tenant->getDatabaseName();

        $this->assertStringNotContainsString('-', $name, 'Schema name must not contain hyphens (PostgreSQL unsafe)');
        $this->assertStringStartsWith('tenant_', $name);
    }

    public function test_database_name_prefix_is_tenant(): void
    {
        $tenant = new Tenant();
        $tenant->id = 'abc123';

        $this->assertSame('tenant_abc123', $tenant->getDatabaseName());
    }

    public function test_database_name_uuid_normalized(): void
    {
        $tenant = new Tenant();
        $tenant->id = 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee';

        $expected = 'tenant_aaaaaaaabbbbccccddddeeeeeeeeeeee';
        $this->assertSame($expected, $tenant->getDatabaseName());
    }
}
