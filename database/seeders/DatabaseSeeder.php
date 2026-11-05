<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database for initial setup & production deployment.
     */
    public function run(): void
    {
        // 1. Seed Default Users (Akun Akses Sistem: Superadmin, Admin Warehouse, Operator Lapangan)
        $users = [
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

        foreach ($users as $u) {
            $userExists = DB::table('users')->where('email', $u['email'])->first();
            if (!$userExists) {
                DB::table('users')->insert(array_merge($u, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            } else {
                DB::table('users')->where('email', $u['email'])->update([
                    'nama_warehouse' => $u['nama_warehouse'],
                    'username' => $u['username'],
                    'role' => $u['role'],
                    'updated_at' => now(),
                ]);
            }
        }

        // 2. Seed Karyawan (Petugas Lapangan & Helper Aktif)
        $karyawans = [
            ['nama' => 'Budi Santoso', 'nomor_induk' => 'KRY001', 'position' => 'operator', 'status' => 'aktif'],
            ['nama' => 'Agus Supriyadi', 'nomor_induk' => 'KRY002', 'position' => 'operator', 'status' => 'aktif'],
            ['nama' => 'Hendra Wijaya', 'nomor_induk' => 'KRY003', 'position' => 'operator', 'status' => 'aktif'],
            ['nama' => 'Fitria Ramadhani Prihandiva', 'nomor_induk' => '357308973', 'position' => 'helper', 'status' => 'aktif'],
        ];

        foreach ($karyawans as $k) {
            $karyawanExists = DB::table('karyawans')->where('nomor_induk', $k['nomor_induk'])->first();
            if (!$karyawanExists) {
                DB::table('karyawans')->insert(array_merge($k, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            } else {
                DB::table('karyawans')->where('nomor_induk', $k['nomor_induk'])->update([
                    'nama' => $k['nama'],
                    'position' => $k['position'],
                    'status' => $k['status'],
                    'updated_at' => now(),
                ]);
            }
        }

        // 3. Seed SOP Photo Points (27 Titik SOP Standar Terkini)
        // Format tuple: [urutan, nama_titik, deskripsi, tipe_item, wajib, perlu_ocr_container, perlu_deteksi_orang, perlu_deteksi_barcode]
        $points = [
            [1, 'Plat Nomor', 'Foto plat nomor truck/container', 'foto', true, false, false, false],
            [2, 'Foto di Loading Area', 'Photo Fiber/Sodium di loading/staging area yang sudah dikumpulkan', 'foto', true, false, false, false],
            [3, 'Foto Bales (Zoomable)', 'Photo Fiber/Sodium di loading/staging area, nomor bales terbaca saat di-zoom', 'foto', true, false, false, false],
            [4, 'Pembersihan Bagian Depan', 'Photo Fiber/Sodium bagian depan sedang dibersihkan', 'foto', true, false, true, false],
            [5, 'Pembersihan Bagian Samping', 'Photo Fiber/Sodium bagian samping sedang dibersihkan', 'foto', true, false, true, false],
            [6, 'Pembersihan Bagian Atas', 'Photo Fiber/Sodium bagian atas sedang dibersihkan', 'foto', true, false, true, false],
            [7, 'Container Luar Kiri', 'Photo container bagian luar sisi kiri, nomor container terbaca', 'foto', true, true, false, false],
            [8, 'Container Luar Kanan', 'Photo container bagian luar sisi kanan dari arah depan container', 'foto', true, false, false, false],
            [9, 'Lantai Container Kosong', 'Photo lantai container dalam keadaan kosong (wajib ada terpal untuk non woven)', 'foto', true, false, false, false],
            [10, 'Bagian Atas Dalam', 'Photo bagian atas dalam container', 'foto', true, false, false, false],
            [11, 'Bagian Kanan Dalam', 'Photo bagian kanan dalam container, nomor container harus terbaca', 'foto', true, true, false, false],
            [12, 'Bagian Kiri Dalam', 'Photo bagian kiri dalam container', 'foto', true, false, false, false],
            [13, 'Swab Test Kiri', 'Photo swab test permukaan dinding kiri menggunakan majun', 'foto', true, false, false, false],
            [14, 'Swab Test Kanan', 'Photo swab test permukaan dinding kanan menggunakan majun', 'foto', true, false, false, false],
            [15, 'Swab Test Depan', 'Photo swab test permukaan dinding depan menggunakan majun', 'foto', true, false, false, false],
            [16, 'Terisi Baris Pertama', 'Photo terisi fiber/bales baris pertama', 'foto', true, false, false, false],
            [17, 'Terisi Setengah', 'Photo terisi fiber/bales setengah container', 'foto', true, false, false, false],
            [18, 'Terisi Penuh', 'Photo terisi fiber/bales penuh dan tersegel', 'foto', true, false, false, false],
            [19, 'Satu Pintu Tertutup', 'Photo tertutup satu pintu, nomor container harus terbaca', 'foto', true, true, false, false],
            [20, 'Tertutup Terkunci', 'Photo container tertutup dan terkunci', 'foto', true, false, false, false],
            [21, 'Tampak Segel & Plat', 'Photo pintu tertutup tampak segel container dan beacukai, nomor container, plat nomor', 'foto', true, false, false, false],
            [22, 'Foto Segel Pelayaran (Zoomable)', 'Foto segel pelayaran', 'foto', true, false, false, false],
            [23, 'Ban Diganjal', 'Photo 2 ban diganjal stopper', 'foto', true, false, false, false],
            [24, 'Checklist Document', 'Truck Checklist (Lokal) / Container Checklist (Export)', 'foto', false, false, false, false], // Opsional
            [25, 'Bales Inspection Ball', 'Bales Inspection Ball', 'foto', false, false, false, false], // Opsional
            [26, 'Surat Jalan dan scanner OK', 'Surat Jalan dan scanner OK', 'foto', true, false, false, false],
            [27, 'Video Singkat', 'Video singkat', 'video', false, false, false, false], // Opsional
        ];

        foreach ($points as $p) {
            $pointData = [
                'jenis_pengiriman' => 'both',
                'nama_titik' => $p[1],
                'deskripsi' => $p[2],
                'tipe_item' => $p[3],
                'wajib' => $p[4],
                'perlu_ocr_container' => $p[5],
                'perlu_deteksi_orang' => $p[6],
                'perlu_deteksi_barcode' => $p[7],
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
        $this->call([
            DemoUsersSeeder::class,
        ]);
    }
}
