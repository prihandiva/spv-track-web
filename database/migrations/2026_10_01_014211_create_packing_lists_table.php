<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packing_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained('shipments')->onDelete('cascade');
            $table->string('packing_list_no')->nullable();
            $table->date('packing_list_date')->nullable();
            $table->string('reference_no')->nullable();
            $table->date('ref_date')->nullable();
            $table->string('order_no')->nullable();
            $table->date('order_date')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('description_produk')->nullable();
            $table->string('hs_code')->nullable();
            $table->string('country_of_origin')->nullable();
            $table->text('raw_ocr_text')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('packing_lists');
    }
};
