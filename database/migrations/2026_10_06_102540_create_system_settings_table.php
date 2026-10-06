<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('group')->default('general'); // general, ai_ocr, evidence, export
            $table->string('type')->default('string'); // string, boolean, integer, json
            $table->string('label');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        $defaultSettings = [
            [
                'key' => 'app_name',
                'value' => 'SPV-Track Logistic Monitoring',
                'group' => 'general',
                'type' => 'string',
                'label' => 'Nama Aplikasi',
                'description' => 'Nama identitas aplikasi yang ditampilkan di header dan dokumen audit.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'company_name',
                'value' => 'PT SPV Evidence Logistics',
                'group' => 'general',
                'type' => 'string',
                'label' => 'Nama Perusahaan / Organisasi',
                'description' => 'Nama entitas pemilik warehouse yang dicetak pada laporan resmi.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'warehouse_default_name',
                'value' => 'Warehouse A (Fiber & Sodium)',
                'group' => 'general',
                'type' => 'string',
                'label' => 'Warehouse Utama',
                'description' => 'Nama warehouse default operasional aktif.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'warehouse_locations',
                'value' => json_encode(['Atas', 'Tengah', 'Bawah']),
                'group' => 'general',
                'type' => 'json',
                'label' => 'Daftar Lokasi Warehouse Staging',
                'description' => 'Pilihan opsi staging area di Field App (Atas, Tengah, Bawah).',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'product_types',
                'value' => json_encode(['Fiber', 'Sodium']),
                'group' => 'general',
                'type' => 'json',
                'label' => 'Komoditas Produk',
                'description' => 'Pilihan produk kargo saat inisiasi sesi staging.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'ocr_auto_validation',
                'value' => '1',
                'group' => 'ai_ocr',
                'type' => 'boolean',
                'label' => 'Auto-OCR Validasi Nomor Container',
                'description' => 'Otomatis mendeteksi teks ISO nomor container saat foto diupload di titik tertentu.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'ai_person_detection',
                'value' => '1',
                'group' => 'ai_ocr',
                'type' => 'boolean',
                'label' => 'AI Deteksi Orang pada Pembersihan Bale',
                'description' => 'Validasi otomatis bahwa petugas terlihat saat SOP pembersihan bale (titik 4, 5, 6).',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'ai_barcode_detection',
                'value' => '1',
                'group' => 'ai_ocr',
                'type' => 'boolean',
                'label' => 'AI Deteksi Area & Barcode Bale',
                'description' => 'Pemeriksaan keberadaan label barcode pada titik foto bales loading area.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'watermark_evidence',
                'value' => '1',
                'group' => 'evidence',
                'type' => 'boolean',
                'label' => 'Watermark Timestamp & Metadata Evidence',
                'description' => 'Sertakan stempel tanggal, jam server WIB, dan status audit di dokumen ekspor PDF.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'zip_naming_format',
                'value' => '{NOMOR_CONTAINER}_EVIDENCE',
                'group' => 'export',
                'type' => 'string',
                'label' => 'Pola Penamaan Arsip ZIP',
                'description' => 'Format nama berkas batch unduhan foto asli ({NOMOR_CONTAINER}, {TANGGAL}, dll).',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('system_settings')->insert($defaultSettings);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
