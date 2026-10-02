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
        Schema::create('shipment_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained('shipments')->onDelete('cascade');
            $table->unsignedInteger('point_no')->nullable()->comment('Null jika foto ekstra');
            $table->string('point_label', 255)->comment('Label nama titik atau foto ekstra');
            $table->boolean('is_extra')->default(false);
            $table->string('path', 500)->comment('Path file asli JPG di private storage');
            $table->string('stamped_path', 500)->comment('Path file bertanda timestamp di private storage');
            $table->string('thumbnail_path', 500)->nullable()->comment('Path thumbnail untuk listing cepat');
            $table->string('mime', 50)->default('image/jpeg');
            $table->unsignedBigInteger('size')->comment('Ukuran file asli dalam byte');
            $table->string('sha256', 64)->comment('SHA-256 hash integritas file asli');
            $table->timestamp('captured_at')->nullable()->comment('Waktu pengambilan foto dari perangkat/EXIF');
            $table->timestamp('stamped_at')->comment('Waktu stempel dari server WIB');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('ocr_text')->nullable()->comment('Hasil ekstraksi OCR jika ada');
            $table->timestamps();

            // Indexes sesuai kebutuhan performa query
            $table->index('shipment_id');
            $table->index(['shipment_id', 'point_no']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipment_photos');
    }
};
