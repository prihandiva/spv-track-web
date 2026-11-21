<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database for initial setup & production deployment.
     */
    public function run(): void
    {
        // 1. Seed Users (Superadmin Dadan, Admin Hendri, Akun Sistem, dan 29 Operator Lapangan)
        $this->call([
            UserSeeder::class,
            KaryawanSeeder::class,
        ]);

        // 2. Seed SOP Photo Points (27 Titik SOP Standar Terkini)
        // Format tuple: [urutan, nama_titik, deskripsi, tipe_item, wajib]
        $points = [
            [1, 'Plat Nomor', 'Foto plat nomor truck/container', 'foto', true],
            [2, 'Foto di Loading Area', 'Photo Fiber/Sodium di loading/staging area yang sudah dikumpulkan', 'foto', true],
            [3, 'Foto Bales (Zoomable)', 'Photo Fiber/Sodium di loading/staging area, nomor bales terbaca saat di-zoom', 'foto', true],
            [4, 'Pembersihan Bagian Depan', 'Photo Fiber/Sodium bagian depan sedang dibersihkan', 'foto', true],
            [5, 'Pembersihan Bagian Samping', 'Photo Fiber/Sodium bagian samping sedang dibersihkan', 'foto', true],
            [6, 'Pembersihan Bagian Atas', 'Photo Fiber/Sodium bagian atas sedang dibersihkan', 'foto', true],
            [7, 'Container Luar Kiri', 'Photo container bagian luar sisi kiri, nomor container terbaca', 'foto', true],
            [8, 'Container Luar Kanan', 'Photo container bagian luar sisi kanan dari arah depan container', 'foto', true],
            [9, 'Lantai Container Kosong', 'Photo lantai container dalam keadaan kosong (wajib ada terpal untuk non woven)', 'foto', true],
            [10, 'Bagian Atas Dalam', 'Photo bagian atas dalam container', 'foto', true],
            [11, 'Bagian Kanan Dalam', 'Photo bagian kanan dalam container, nomor container harus terbaca', 'foto', true],
            [12, 'Bagian Kiri Dalam', 'Photo bagian kiri dalam container', 'foto', true],
            [13, 'Swab Test Kiri', 'Photo swab test permukaan dinding kiri menggunakan majun', 'foto', true],
            [14, 'Swab Test Kanan', 'Photo swab test permukaan dinding kanan menggunakan majun', 'foto', true],
            [15, 'Swab Test Depan', 'Photo swab test permukaan dinding depan menggunakan majun', 'foto', true],
            [16, 'Terisi Baris Pertama', 'Photo terisi fiber/bales baris pertama', 'foto', true],
            [17, 'Terisi Setengah', 'Photo terisi fiber/bales setengah container', 'foto', true],
            [18, 'Terisi Penuh', 'Photo terisi fiber/bales penuh dan tersegel', 'foto', true],
            [19, 'Satu Pintu Tertutup', 'Photo tertutup satu pintu, nomor container harus terbaca', 'foto', true],
            [20, 'Tertutup Terkunci', 'Photo container tertutup dan terkunci', 'foto', true],
            [21, 'Tampak Segel & Plat', 'Photo pintu tertutup tampak segel container dan beacukai, nomor container, plat nomor', 'foto', true],
            [22, 'Foto Segel Pelayaran (Zoomable)', 'Foto segel pelayaran', 'foto', true],
            [23, 'Ban Diganjal', 'Photo 2 ban diganjal stopper', 'foto', true],
            [24, 'Checklist Document', 'Truck Checklist (Lokal) / Container Checklist (Export)', 'foto', false], // Opsional
            [25, 'Bales Inspection Ball', 'Bales Inspection Ball', 'foto', false], // Opsional
            [26, 'Surat Jalan dan scanner OK', 'Surat Jalan dan scanner OK', 'foto', true],
            [27, 'Video Singkat', 'Video singkat', 'video', false], // Opsional
        ];

        foreach ($points as $p) {
            $pointData = [
                'jenis_pengiriman' => 'both',
                'nama_titik' => $p[1],
                'deskripsi' => $p[2],
                'tipe_item' => $p[3],
                'wajib' => $p[4],
                'updated_at' => now(),
            ];

            $pointExists = DB::table('sop_photo_points')->where('urutan', $p[0])->exists();
            if (!$pointExists) {
                DB::table('sop_photo_points')->insert(array_merge([
                    'urutan' => $p[0],
                    'created_at' => now(),
                ], $pointData));
            } else {
                DB::table('sop_photo_points')->where('urutan', $p[0])->update($pointData);
            }
        }
    }
}
