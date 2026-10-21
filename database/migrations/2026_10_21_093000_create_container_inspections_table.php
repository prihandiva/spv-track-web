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
        Schema::create('container_inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained('shipments')->onDelete('cascade')->unique();
            
            // Header Info
            $table->date('loading_date')->nullable();
            $table->string('loading_start_time', 10)->nullable();
            $table->string('loading_finish_time', 10)->nullable();
            $table->string('nomor_kontainer')->nullable();
            $table->string('nomor_seal')->nullable();
            $table->string('nama_emkl_pelayaran')->nullable();
            $table->string('nama_petugas_inspeksi')->nullable();

            // 6 Titik Inspeksi (true = Ya / Bersih / Lolos, false = Tidak / Gagal)
            $table->boolean('pintu_status')->default(true);
            $table->text('pintu_catatan')->nullable();

            $table->boolean('dinding_kanan_status')->default(true);
            $table->text('dinding_kanan_catatan')->nullable();

            $table->boolean('dinding_kiri_status')->default(true);
            $table->text('dinding_kiri_catatan')->nullable();

            $table->boolean('dinding_depan_status')->default(true);
            $table->text('dinding_depan_catatan')->nullable();

            $table->boolean('atap_status')->default(true);
            $table->text('atap_catatan')->nullable();

            $table->boolean('lantai_status')->default(true);
            $table->text('lantai_catatan')->nullable();

            // Kesimpulan & Pengesahan
            $table->enum('kesimpulan', ['layak', 'tidak_layak', 'layak_dengan_catatan'])->default('layak');
            $table->text('catatan')->nullable();
            $table->string('nama_inspektor')->nullable();
            $table->date('tanggal_inspeksi')->nullable();
            $table->text('tanda_tangan')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('container_inspections');
    }
};
