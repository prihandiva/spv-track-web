<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sop_photo_points', function (Blueprint $table) {
            $table->id();
            $table->enum('jenis_pengiriman', ['export', 'lokal', 'both']);
            $table->integer('urutan');
            $table->string('nama_titik');
            $table->text('deskripsi')->nullable();
            $table->enum('tipe_item', ['foto', 'dokumen', 'video']);
            $table->boolean('wajib')->default(true);
            $table->boolean('perlu_ocr_container')->default(false);
            $table->boolean('perlu_deteksi_orang')->default(false);
            $table->boolean('perlu_deteksi_barcode')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sop_photo_points');
    }
};
