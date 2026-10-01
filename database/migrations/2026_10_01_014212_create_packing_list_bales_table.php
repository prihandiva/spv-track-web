<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packing_list_bales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('packing_list_id')->constrained('packing_lists')->onDelete('cascade');
            $table->string('bale_no');
            $table->decimal('gross_kg', 8, 2)->nullable();
            $table->decimal('net_kg', 8, 2)->nullable();
            $table->decimal('cond_kg', 8, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('packing_list_bales');
    }
};
