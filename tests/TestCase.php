<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Only run central migrations in tests to avoid conflicts with tenant
     * migrations (both create a 'users' table in the same SQLite DB).
     *
     * In Laravel 11, in-memory SQLite uses `migrate` (not `migrate:fresh`),
     * so we must override `migrateUsing()` to restrict the migration path.
     */
    protected function migrateUsing(): array
    {
        return ['--path' => 'database/migrations/central'];
    }
}
