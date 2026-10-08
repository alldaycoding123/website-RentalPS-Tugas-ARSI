<?php

namespace Database\Seeders;

use App\Models\Staff;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StaffSeeder extends Seeder
{
    /**
     * Akun staff demo (password: password123).
     */
    public function run(): void
    {
        $data = [
            ['nama' => 'Rina Kasir', 'email' => 'kasir@psrental.test', 'role' => 'KASIR', 'no_hp' => '081111111111'],
            ['nama' => 'Agus Admin', 'email' => 'admin@psrental.test', 'role' => 'ADMIN', 'no_hp' => '082222222222'],
            ['nama' => 'Budi Manajer', 'email' => 'manajer@psrental.test', 'role' => 'MANAJER', 'no_hp' => '083333333333'],
        ];

        foreach ($data as $staff) {
            Staff::updateOrCreate(
                ['email' => $staff['email']],
                [
                    'nama' => $staff['nama'],
                    'password' => Hash::make('password123'),
                    'role' => $staff['role'],
                    'no_hp' => $staff['no_hp'],
                    'aktif' => true,
                ]
            );
        }
    }
}
