<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('karyawan_id')->constrained('karyawans')->onDelete('cascade');
            $table->enum('jenis_produk', ['fiber', 'sodium']);
            $table->enum('jenis_pengiriman', ['export', 'lokal']);
            $table->string('nomor_container_atau_plat');
            $table->enum('cuaca', ['kering', 'mendung', 'hujan', 'gerimis']);
            $table->enum('waktu', ['siang', 'sore', 'malam']);
            $table->date('tanggal_staging');
            $table->enum('warehouse_lokasi', ['atas', 'tengah', 'bawah']);
            $table->enum('status', ['draft', 'submitted'])->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
