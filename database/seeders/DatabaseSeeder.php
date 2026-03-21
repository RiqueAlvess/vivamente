<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CentralAdminSeeder::class,
            DemoTenantSeeder::class,
        ]);
    }
}
