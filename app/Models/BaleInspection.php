<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BaleInspection extends Model
{
    protected $fillable = [
        'shipment_id',
        'loading_date',
        'loading_start_time',
        'loading_finish_time',
        'nama_petugas_loading',
        'nomor_surat_jalan',
        'nama_pelanggan',
        'jumlah_bale',
        'nomor_kontainer_truk',
        'jenis_bale',
        'jenis_label',
        'first_eye_nama',
        'first_eye_jabatan',
        'first_eye_tanggal',
        'first_eye_signature',
        'second_eye_status',
        'second_eye_tindakan',
        'second_eye_bale_awal',
        'second_eye_bale_pengganti',
        'second_eye_catatan',
        'second_eye_nama',
        'second_eye_jabatan',
        'second_eye_tanggal',
        'second_eye_signature',
        'sumber_bale',
        'sumber_bale_lainnya',
        'stagging_2_rubber_cone',
        'lokasi_stagging',
        'stagging_catatan',
        'cuaca',
        'waktu',
        'kesimpulan_akhir',
    ];

    protected function casts(): array
    {
        return [
            'loading_date' => 'date',
            'first_eye_tanggal' => 'date',
            'second_eye_tanggal' => 'date',
            'jenis_bale' => 'array',
            'jenis_label' => 'array',
            'stagging_2_rubber_cone' => 'boolean',
        ];
    }

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(BaleInspectionItem::class)->orderBy('item_index');
    }

    /**
     * Hitung total ketidaksesuaian yang ditemukan
     */
    public function countIssues(): int
    {
        return $this->items()->where('ada_ketidaksesuaian', true)->count();
    }

    /**
     * Label kesimpulan akhir human-readable
     */
    public function getKesimpulanLabelAttribute(): string
    {
        return match ($this->kesimpulan_akhir) {
            'layak' => 'Layak untuk proses loading / stuffing',
            'layak_dengan_catatan' => 'Layak dengan catatan (lihat bagian C.2)',
            'tidak_layak' => 'Tidak layak (ditunda / diganti)',
            default => ucfirst(str_replace('_', ' ', $this->kesimpulan_akhir ?? 'Belum Ditentukan')),
        };
    }
}
