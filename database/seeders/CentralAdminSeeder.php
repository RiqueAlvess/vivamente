<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CentralAdminSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@plataformanr1.com'],
            [
                'name' => 'Administrador Global',
                'password' => Hash::make('admin@123'),
                'role' => 'global_admin',
                'is_active' => true,
            ]
        );

        $this->command->info('Administrador global criado: admin@plataformanr1.com');
    }
}
