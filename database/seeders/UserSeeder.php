<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Akun default sesuai PRD §9 DoD F0.
 *
 * PENTING: A16 — operator lapangan memakai SATU AKUN BERSAMA per shift,
 * bukan akun per orang. Karena itu operator_nama di transaksi tetap
 * diisi manual, terpisah dari created_by.
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        $akun = [
            [
                'name' => 'Administrator Gudang',
                'username' => 'admin',
                'email' => 'admin@wms.local',
                'role' => Role::Admin,
            ],
            [
                'name' => 'Operator Gudang',
                'username' => 'operator',
                'email' => 'operator@wms.local',
                'role' => Role::Operator,
            ],
            [
                'name' => 'Manajer DTY PEI',
                'username' => 'manajer',
                'email' => 'manajer@wms.local',
                'role' => Role::Manajer,
            ],
        ];

        foreach ($akun as $data) {
            User::updateOrCreate(
                ['username' => $data['username']],
                [
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => Hash::make('password'),
                    'role' => $data['role'],
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
