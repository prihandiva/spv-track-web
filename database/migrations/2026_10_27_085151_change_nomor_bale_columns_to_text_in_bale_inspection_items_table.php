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
        Schema::table('bale_inspection_items', function (Blueprint $table) {
            $table->text('nomor_bale_awal')->nullable()->change();
            $table->text('nomor_bale_pengganti')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bale_inspection_items', function (Blueprint $table) {
            $table->string('nomor_bale_awal', 255)->nullable()->change();
            $table->string('nomor_bale_pengganti', 255)->nullable()->change();
        });
    }
};
