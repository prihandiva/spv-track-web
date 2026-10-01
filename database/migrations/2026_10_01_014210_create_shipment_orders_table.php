<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipment_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained('shipments')->onDelete('cascade');
            $table->string('shipment_group')->nullable();
            $table->string('shipment_no')->nullable();
            $table->text('nama_customer')->nullable();
            $table->text('alamat_customer')->nullable();
            $table->text('forwarding_agent')->nullable();
            $table->string('package_start_loading')->nullable();
            $table->string('end_of_shipment')->nullable();
            $table->decimal('freight_rate', 10, 2)->nullable();
            $table->text('raw_ocr_text')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipment_orders');
    }
};
