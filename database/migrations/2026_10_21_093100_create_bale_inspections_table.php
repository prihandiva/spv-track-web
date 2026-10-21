<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bale_inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained('shipments')->onDelete('cascade')->unique();

            // Header Info
            $table->date('loading_date')->nullable();
            $table->string('loading_start_time', 10)->nullable();
            $table->string('loading_finish_time', 10)->nullable();
            $table->string('nama_petugas_loading')->nullable();

            // A. Informasi Umum
            $table->string('nomor_surat_jalan')->nullable();
            $table->string('nama_pelanggan')->nullable();
            $table->integer('jumlah_bale')->nullable();
            $table->string('nomor_kontainer_truk')->nullable();
            $table->json('jenis_bale')->nullable(); // e.g. ["tx_ecovero", "tx_non_ecovero", "nw"]
            $table->json('jenis_label')->nullable(); // e.g. ["tanpa_label", "fsc", "isi", "n", "o", "a"]

            // C. Verifikasi 4 Eyes Principle
            // 1. First Eye (Pemeriksa Pertama)
            $table->string('first_eye_nama')->nullable();
            $table->string('first_eye_jabatan')->nullable();
            $table->date('first_eye_tanggal')->nullable();
            $table->text('first_eye_signature')->nullable();

            // 2. Second Eye (Pemeriksa Kedua / Verifikator)
            $table->enum('second_eye_status', ['disetujui', 'perlu_perbaikan', 'tidak_disetujui'])->nullable();
            $table->string('second_eye_tindakan')->nullable();
            $table->string('second_eye_bale_awal')->nullable();
            $table->string('second_eye_bale_pengganti')->nullable();
            $table->text('second_eye_catatan')->nullable();
            $table->string('second_eye_nama')->nullable();
            $table->string('second_eye_jabatan')->nullable();
            $table->date('second_eye_tanggal')->nullable();
            $table->text('second_eye_signature')->nullable();

            // D. Sumber Bale
            $table->string('sumber_bale')->nullable(); // inspeksi_join_checking, produksi_baru, lainnya
            $table->string('sumber_bale_lainnya')->nullable();

            // E. Lokasi Stagging
            $table->boolean('stagging_2_rubber_cone')->default(true);
            $table->string('lokasi_stagging')->nullable();
            $table->text('stagging_catatan')->nullable();

            // F. Kondisi Proses Pemuatan / Loading
            $table->enum('cuaca', ['kering', 'mendung', 'gerimis', 'hujan'])->nullable();
            $table->enum('waktu', ['siang', 'sore', 'malam'])->nullable();

            // Kesimpulan Akhir
            $table->enum('kesimpulan_akhir', ['layak', 'layak_dengan_catatan', 'tidak_layak'])->default('layak');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bale_inspections');
    }
};
