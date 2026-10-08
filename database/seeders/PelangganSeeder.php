<?php

namespace Database\Seeders;

use App\Models\Pelanggan;
use Illuminate\Database\Seeder;

class PelangganSeeder extends Seeder
{
    /**
     * Pelanggan dummy (termasuk No. HP demo 081234567890).
     */
    public function run(): void
    {
        $pelanggans = [
            ['nama' => 'Pelanggan Demo', 'no_hp' => '081234567890'],
            ['nama' => 'Andi Pratama', 'no_hp' => '081234567891'],
            ['nama' => 'Siti Nurhaliza', 'no_hp' => '081234567892'],
            ['nama' => 'Rudi Hartono', 'no_hp' => '081234567893'],
            ['nama' => 'Dewi Lestari', 'no_hp' => '081234567894'],
        ];

        foreach ($pelanggans as $pelanggan) {
            Pelanggan::updateOrCreate(['no_hp' => $pelanggan['no_hp']], $pelanggan);
        }
    }
}
