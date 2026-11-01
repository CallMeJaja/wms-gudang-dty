<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Akun awal untuk administrator, operator, dan manajer.
 *
 * Operator lapangan memakai satu akun bersama per shift, bukan akun
 * per orang. Karena itu operator_nama pada transaksi tetap diisi manual
 * dan terpisah dari created_by.
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
