<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evidence_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained('shipments')->onDelete('cascade');
            $table->foreignId('sop_photo_point_id')->nullable()->constrained('sop_photo_points')->onDelete('set null');
            $table->string('file_path');
            $table->string('file_name');
            $table->enum('tipe_item', ['foto', 'dokumen', 'video']);
            $table->timestamp('captured_at')->nullable();
            $table->decimal('gps_lat', 10, 8)->nullable();
            $table->decimal('gps_lng', 11, 8)->nullable();
            $table->boolean('is_tambahan')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evidence_items');
    }
};
