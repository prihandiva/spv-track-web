<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Karyawan (Petugas Lapangan)
        $karyawans = [
            ['nama' => 'Budi Santoso', 'nomor_induk' => 'KRY001', 'status' => 'aktif', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Agus Supriyadi', 'nomor_induk' => 'KRY002', 'status' => 'aktif', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Hendra Wijaya', 'nomor_induk' => 'KRY003', 'status' => 'aktif', 'created_at' => now(), 'updated_at' => now()],
        ];
        DB::table('karyawans')->insert($karyawans);

        // 2. Seed Admin User
        DB::table('users')->insert([
            'nama_warehouse' => 'Warehouse A',
            'email' => 'admin@spvtrack.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. Seed SOP Photo Points (27 Titik)
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
            [21, 'Tampak Segel & Plat', 'Photo pintu tertutup tampak segel, nomor container, plat nomor, pelayaran', 'foto', true, true, false, false],
            [22, 'Foto Segel', 'Foto segel', 'foto', true, false, false, false],
            [23, 'Ban Diganjal', 'Photo 2 ban diganjal stopper', 'foto', true, false, false, false],
            [24, 'Checklist Document', 'Truck Checklist (Lokal) / Container Checklist (Export)', 'dokumen', true, false, false, false],
            [25, 'Bales Inspection Ball', 'Bales Inspection Ball', 'dokumen', true, false, false, false],
            [26, 'Surat Jalan', 'Surat Jalan + scan OK', 'dokumen', true, false, false, false],
            [27, 'Video Singkat', 'Video singkat', 'video', false, false, false, false], // tidak wajib
        ];

        $insertPoints = [];
        foreach ($points as $p) {
            $insertPoints[] = [
                'jenis_pengiriman' => 'both',
                'urutan' => $p[0],
                'nama_titik' => $p[1],
                'deskripsi' => $p[2],
                'tipe_item' => $p[3],
                'wajib' => $p[4],
                'perlu_ocr_container' => $p[5],
                'perlu_deteksi_orang' => $p[6],
                'perlu_deteksi_barcode' => $p[7],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('sop_photo_points')->insert($insertPoints);
    }
}
