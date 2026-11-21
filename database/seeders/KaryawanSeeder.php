<?php

namespace Database\Seeders;

use App\Models\Karyawan;
use Illuminate\Database\Seeder;

class KaryawanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 29 Operator Lapangan Unik
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

        foreach ($operators as $index => $nama) {
            $nik = sprintf('OPR%03d', $index + 1);

            $existing = Karyawan::whereRaw('LOWER(nama) = ?', [strtolower($nama)])->first();
            if ($existing) {
                $existing->update([
                    'position' => 'operator',
                    'status' => 'aktif',
                ]);
            } else {
                Karyawan::updateOrCreate(
                    ['nomor_induk' => $nik],
                    [
                        'nama' => $nama,
                        'position' => 'operator',
                        'status' => 'aktif',
                    ]
                );
            }
        }
    }
}
