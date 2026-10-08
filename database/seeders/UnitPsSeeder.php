<?php

namespace Database\Seeders;

use App\Models\UnitPs;
use Illuminate\Database\Seeder;

class UnitPsSeeder extends Seeder
{
    /**
     * Data unit PlayStation.
     */
    public function run(): void
    {
        $units = [
            ['kode_unit' => 'PS5-01', 'nama' => 'PlayStation 5', 'tipe' => 'PS5', 'ruang' => 'Ruang A', 'tv' => 'TV 65" 4K OLED, Headset Premium', 'harga_jam' => 15000, 'status' => 'TERSEDIA'],
            ['kode_unit' => 'PS5-02', 'nama' => 'PS5 Digital', 'tipe' => 'PS5', 'ruang' => 'Ruang A', 'tv' => 'TV 55" 4K UHD, 2 Controller', 'harga_jam' => 12000, 'status' => 'TERSEDIA'],
            ['kode_unit' => 'PS4-01', 'nama' => 'PS4 Pro', 'tipe' => 'PS4', 'ruang' => 'Ruang B', 'tv' => 'TV 43" Full HD, 2 Controller', 'harga_jam' => 8000, 'status' => 'TERSEDIA'],
            ['kode_unit' => 'PS4-02', 'nama' => 'PS4 Slim', 'tipe' => 'PS4', 'ruang' => 'Ruang B', 'tv' => 'TV 32" Full HD, 1 Controller', 'harga_jam' => 6000, 'status' => 'TERSEDIA'],
            ['kode_unit' => 'PS5-03', 'nama' => 'PlayStation 5', 'tipe' => 'PS5', 'ruang' => 'Ruang C', 'tv' => 'TV 55" 4K UHD (servis controller)', 'harga_jam' => 15000, 'status' => 'MAINTENANCE'],
            ['kode_unit' => 'PS4-03', 'nama' => 'PS4 Slim', 'tipe' => 'PS4', 'ruang' => 'Ruang C', 'tv' => 'TV 43" Full HD, 2 Controller', 'harga_jam' => 7000, 'status' => 'TERSEDIA'],
        ];

        foreach ($units as $unit) {
            UnitPs::updateOrCreate(['kode_unit' => $unit['kode_unit']], $unit);
        }
    }
}
