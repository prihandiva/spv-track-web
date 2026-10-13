<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accounts = [
            [
                'nama_warehouse' => 'Super Administrator',
                'username' => 'superadmin',
                'email' => 'superadmin@spvtrack.com',
                'password' => Hash::make('password'),
                'role' => 'superadmin',
            ],
            [
                'nama_warehouse' => 'Admin Warehouse',
                'username' => 'admin',
                'email' => 'admin@spvtrack.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ],
            [
                'nama_warehouse' => 'Operator Lapangan',
                'username' => 'operator',
                'email' => 'operator@spvtrack.com',
                'password' => Hash::make('password'),
                'role' => 'operator',
            ],
        ];

        foreach ($accounts as $acc) {
            User::updateOrCreate(
                ['email' => $acc['email']],
                $acc
            );
        }
    }
}
