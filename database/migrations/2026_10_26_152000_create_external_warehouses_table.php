<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('external_warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('nama')->unique();
            $table->string('kode', 50)->nullable();
            $table->text('alamat')->nullable();
            $table->string('pic_nama')->nullable();
            $table->string('pic_kontak')->nullable();
            $table->string('status', 20)->default('aktif'); // 'aktif' | 'nonaktif'
            $table->text('catatan')->nullable();
            $table->timestamps();
        });

        // Seed initial standard external warehouses into database
        $initialWarehouses = [
            [
                'nama' => 'PT Kamadjaja Logistics',
                'kode' => 'KMJ',
                'status' => 'aktif',
                'catatan' => 'Mitra pergudangan logistik nasional',
            ],
            [
                'nama' => 'PT Dunia Express Transindo',
                'kode' => 'DUNEX',
                'status' => 'aktif',
                'catatan' => 'Mitra pergudangan dan transportasi',
            ],
            [
                'nama' => 'PT Samudera Indonesia Logistics',
                'kode' => 'SIL',
                'status' => 'aktif',
                'catatan' => 'Mitra logistik dan pengapalan',
            ],
            [
                'nama' => 'PT Puninar Logistics',
                'kode' => 'PNR',
                'status' => 'aktif',
                'catatan' => 'Mitra logistik terpadu dan kargo',
            ],
            [
                'nama' => 'PT Linfox Logistics Indonesia',
                'kode' => 'LFX',
                'status' => 'aktif',
                'catatan' => 'Mitra pergudangan multinasional',
            ],
            [
                'nama' => 'PT Ceva Logistics Indonesia',
                'kode' => 'CEVA',
                'status' => 'aktif',
                'catatan' => 'Mitra kargo kontainer global',
            ],
        ];

        foreach ($initialWarehouses as $wh) {
            DB::table('external_warehouses')->updateOrInsert(
                ['nama' => $wh['nama']],
                array_merge($wh, ['created_at' => now(), 'updated_at' => now()])
            );
        }

        // Import existing unique external warehouse names from shipments if any exist
        try {
            $existingHistory = DB::table('shipments')
                ->where('warehouse_type', 'external')
                ->whereNotNull('warehouse_lokasi')
                ->where('warehouse_lokasi', '!=', '')
                ->distinct()
                ->pluck('warehouse_lokasi');

            foreach ($existingHistory as $loc) {
                $cleanName = trim((string) $loc);
                if ($cleanName !== '' && ! in_array(strtolower($cleanName), ['atas', 'tengah', 'bawah'], true)) {
                    DB::table('external_warehouses')->updateOrInsert(
                        ['nama' => $cleanName],
                        [
                            'status' => 'aktif',
                            'catatan' => 'Diimpor otomatis dari riwayat shipment',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                }
            }
        } catch (\Throwable $e) {
            // Abaikan jika tabel shipments belum ada pada fresh migration
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('external_warehouses');
    }
};
