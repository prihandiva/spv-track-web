<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE shipments DROP CONSTRAINT IF EXISTS shipments_jenis_produk_check;');
            DB::statement('ALTER TABLE shipments DROP CONSTRAINT IF EXISTS shipments_warehouse_lokasi_check;');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to restore legacy enum checks
    }
};
