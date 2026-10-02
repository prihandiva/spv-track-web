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
        Schema::table('shipments', function (Blueprint $table) {
            $table->string('shipment_group')->nullable()->after('karyawan_id');
            $table->string('shipment_no')->nullable()->after('shipment_group');
            $table->string('plat_nomor')->nullable()->after('shipment_no');
            $table->string('nama_sopir')->nullable()->after('plat_nomor');
            $table->string('packing_list_no')->nullable()->after('nama_sopir');
            $table->text('tujuan_pengiriman')->nullable()->after('packing_list_no');
            $table->text('agen_forwarding')->nullable()->after('tujuan_pengiriman');
            $table->dateTime('waktu_kedatangan_container')->nullable()->after('agen_forwarding');
            $table->dateTime('waktu_keberangkatan_container')->nullable()->after('waktu_kedatangan_container');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropColumn([
                'shipment_group',
                'shipment_no',
                'plat_nomor',
                'nama_sopir',
                'packing_list_no',
                'tujuan_pengiriman',
                'agen_forwarding',
                'waktu_kedatangan_container',
                'waktu_keberangkatan_container',
            ]);
        });
    }
};
