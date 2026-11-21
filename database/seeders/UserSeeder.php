<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultPassword = Hash::make('password');

        // 1. Akun Superadmin & Admin
        $managementUsers = [
            [
                'nama_warehouse' => 'DADAN NURHIDAYAT',
                'username' => 'dadan',
                'email' => 'dadan@spvtrack.com',
                'password' => $defaultPassword,
                'role' => 'superadmin',
            ],
            [
                'nama_warehouse' => 'HENDRI PURNOMO',
                'username' => 'hendri',
                'email' => 'hendri@spvtrack.com',
                'password' => $defaultPassword,
                'role' => 'admin',
            ],
            // Akun demo / sistem untuk kelancaran automated testing & fallback
            [
                'nama_warehouse' => 'Super Administrator',
                'username' => 'superadmin',
                'email' => 'superadmin@spvtrack.com',
                'password' => $defaultPassword,
                'role' => 'superadmin',
            ],
            [
                'nama_warehouse' => 'Admin Warehouse',
                'username' => 'admin',
                'email' => 'admin@spvtrack.com',
                'password' => $defaultPassword,
                'role' => 'admin',
            ],
            [
                'nama_warehouse' => 'Operator Lapangan',
                'username' => 'operator',
                'email' => 'operator@spvtrack.com',
                'password' => $defaultPassword,
                'role' => 'operator',
            ],
        ];

        foreach ($managementUsers as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                $user
            );
        }

        // 2. Operator Lapangan (29 Operator Unik)
        $operators = [
            'Agus Rohiman',
            'Ahmad Junaedi',
            'Alfero Rizki Ramadhan',
            'Andri Fany Kustiawan',
            'Aries Kuswandana',
            'Barri',
            'Dani Mulyana',
            'Dede Jaenudin',
            'Dendo Rahmadi',
            'Diana',
            'Edeng',
            'Eka Prasetya',
            'Firmansyah',
            'Karna',
            'Kiki Firmansyah',
            'Muhammad Ikhsan',
            'Muhammad Ilham Ramadan',
            'Novi Anwar',
            'Pendik Yulianto',
            'Rahman Bahrudin',
            'Saeful Fajar Sidik',
            'Setio Budi',
            'Slamet Liehardjo',
            'Surono',
            'Tri Setyargo',
            'Ujang Heri',
            'Ujang Jamaludin',
            'Ukas',
            'Wahyudin',
        ];

        foreach ($operators as $nama) {
            $username = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '.', $nama), '.'));
            $email = "{$username}@spvtrack.com";

            User::updateOrCreate(
                ['email' => $email],
                [
                    'nama_warehouse' => $nama,
                    'username' => $username,
                    'password' => $defaultPassword,
                    'role' => 'operator',
                ]
            );
        }
    }
}
