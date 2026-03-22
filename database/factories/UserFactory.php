<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name'           => fake()->name(),
            'email'          => fake()->unique()->safeEmail(),
            'password'       => Hash::make('password'),
            'role'           => 'global_admin',
            'is_active'      => true,
            'login_attempts' => 0,
            'locked_until'   => null,
        ];
    }
}
