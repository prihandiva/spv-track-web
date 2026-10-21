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
            if (! Schema::hasColumn('shipments', 'warehouse_type')) {
                $table->string('warehouse_type', 30)->default('internal')->after('tanggal_staging');
            }
            $table->string('warehouse_lokasi', 255)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            if (Schema::hasColumn('shipments', 'warehouse_type')) {
                $table->dropColumn('warehouse_type');
            }
        });
    }
};
