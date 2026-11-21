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
        // 1. Tambahkan kolom integritas dan audit ke tabel utama evidence_items
        Schema::table('evidence_items', function (Blueprint $table) {
            if (! Schema::hasColumn('evidence_items', 'sha256')) {
                $table->string('sha256', 64)->nullable()->after('file_name')->comment('Hash SHA-256 integritas anti-tamper');
            }
            if (! Schema::hasColumn('evidence_items', 'size')) {
                $table->unsignedBigInteger('size')->nullable()->after('sha256')->comment('Ukuran file dalam bytes');
            }
            if (! Schema::hasColumn('evidence_items', 'uploaded_by')) {
                $table->foreignId('uploaded_by')->nullable()->after('is_tambahan')->constrained('users')->nullOnDelete();
            }
        });

        // 2. Hapus tabel ghost sesuai urutan ketergantungan relasi
        Schema::dropIfExists('ocr_results');
        Schema::dropIfExists('packing_list_bales');
        Schema::dropIfExists('packing_lists');
        Schema::dropIfExists('shipment_orders');
        Schema::dropIfExists('shipment_photos');

        // 3. Hapus kolom flag AI/OCR yang sudah tidak digunakan di master SOP
        Schema::table('sop_photo_points', function (Blueprint $table) {
            $colsToDrop = [];
            foreach (['perlu_ocr_container', 'perlu_deteksi_orang', 'perlu_deteksi_barcode'] as $col) {
                if (Schema::hasColumn('sop_photo_points', $col)) {
                    $colsToDrop[] = $col;
                }
            }
            if (! empty($colsToDrop)) {
                $table->dropColumn($colsToDrop);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-create columns on evidence_items
        Schema::table('evidence_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('uploaded_by');
            $table->dropColumn(['sha256', 'size']);
        });

        // Re-add AI flags on sop_photo_points
        Schema::table('sop_photo_points', function (Blueprint $table) {
            $table->boolean('perlu_ocr_container')->default(false);
            $table->boolean('perlu_deteksi_orang')->default(false);
            $table->boolean('perlu_deteksi_barcode')->default(false);
        });
    }
};
