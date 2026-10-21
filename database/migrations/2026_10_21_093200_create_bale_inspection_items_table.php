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
        Schema::create('bale_inspection_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bale_inspection_id')->constrained('bale_inspections')->onDelete('cascade');
            $table->unsignedTinyInteger('item_index'); // 1 s/d 8
            $table->string('jenis_ketidaksesuaian'); // Sobek, Ada tambalan, Kotor / Bernoda, etc.
            $table->boolean('ada_ketidaksesuaian')->default(false);
            $table->integer('jumlah_bale')->nullable();
            $table->string('tindakan_perbaikan')->nullable(); // diganti, diperbaiki, ganti_label, lainnya
            $table->string('tindakan_lainnya')->nullable();
            $table->string('nomor_bale_awal')->nullable();
            $table->string('nomor_bale_pengganti')->nullable();
            $table->timestamps();

            $table->index(['bale_inspection_id', 'item_index']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bale_inspection_items');
    }
};
