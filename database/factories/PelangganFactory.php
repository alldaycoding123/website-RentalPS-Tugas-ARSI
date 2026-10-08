<?php

namespace Database\Factories;

use App\Models\Pelanggan;
use Illuminate\Database\Eloquent\Factories\Factory;

class PelangganFactory extends Factory
{
    protected $model = Pelanggan::class;

    public function definition(): array
    {
        return [
            'nama' => fake()->name(),
            'no_hp' => '08'.fake()->unique()->numerify('##########'),
        ];
    }
}
