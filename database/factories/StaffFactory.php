<?php

namespace Database\Factories;

use App\Models\Staff;
use Illuminate\Database\Eloquent\Factories\Factory;

class StaffFactory extends Factory
{
    protected $model = Staff::class;

    public function definition(): array
    {
        return [
            'nama' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => 'password123', // di-hash otomatis oleh cast `hashed`
            'role' => fake()->randomElement(['KASIR', 'ADMIN', 'MANAJER']),
            'no_hp' => '08'.fake()->numerify('##########'),
            'aktif' => true,
        ];
    }
}
