<?php

namespace Database\Factories;

use App\Models\UnitPs;
use Illuminate\Database\Eloquent\Factories\Factory;

class UnitPsFactory extends Factory
{
    protected $model = UnitPs::class;

    public function definition(): array
    {
        $tipe = fake()->randomElement(['PS4', 'PS5']);

        return [
            'kode_unit' => strtoupper($tipe.'-'.fake()->unique()->numerify('##')),
            'nama' => $tipe === 'PS5' ? 'PlayStation 5' : 'PlayStation 4',
            'tipe' => $tipe,
            'ruang' => 'Ruang '.fake()->randomElement(['A', 'B', 'C']),
            'tv' => 'TV 43" Full HD, 2 Controller',
            'harga_jam' => $tipe === 'PS5' ? 15000 : 8000,
            'status' => 'TERSEDIA',
        ];
    }
}
