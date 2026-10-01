<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ocr_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evidence_item_id')->constrained('evidence_items')->onDelete('cascade');
            $table->string('detected_container_no')->nullable();
            $table->boolean('cocok_dengan_input')->nullable();
            $table->decimal('confidence_score', 5, 2)->nullable();
            $table->boolean('orang_terdeteksi')->default(false);
            $table->boolean('barcode_terdeteksi')->default(false);
            $table->string('barcode_value')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ocr_results');
    }
};
