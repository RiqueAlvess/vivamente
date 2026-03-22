<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Only run central migrations in tests to avoid conflicts with tenant
     * migrations (both create a 'users' table in the same SQLite DB).
     */
    protected function migrateFreshUsing()
    {
        return array_merge(parent::migrateFreshUsing(), [
            '--path' => 'database/migrations/central',
        ]);
    }
}
