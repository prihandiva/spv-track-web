<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContainerInspection extends Model
{
    protected $fillable = [
        'shipment_id',
        'loading_date',
        'loading_start_time',
        'loading_finish_time',
        'nomor_kontainer',
        'nomor_seal',
        'nama_emkl_pelayaran',
        'nama_petugas_inspeksi',
        'pintu_status',
        'pintu_catatan',
        'dinding_kanan_status',
        'dinding_kanan_catatan',
        'dinding_kiri_status',
        'dinding_kiri_catatan',
        'dinding_depan_status',
        'dinding_depan_catatan',
        'atap_status',
        'atap_catatan',
        'lantai_status',
        'lantai_catatan',
        'kesimpulan',
        'catatan',
        'nama_inspektor',
        'tanggal_inspeksi',
        'tanda_tangan',
    ];

    protected function casts(): array
    {
        return [
            'loading_date' => 'date',
            'tanggal_inspeksi' => 'date',
            'pintu_status' => 'boolean',
            'dinding_kanan_status' => 'boolean',
            'dinding_kiri_status' => 'boolean',
            'dinding_depan_status' => 'boolean',
            'atap_status' => 'boolean',
            'lantai_status' => 'boolean',
        ];
    }

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }

    /**
     * Hitung total titik yang gagal (status = false)
     */
    public function countFailedPoints(): int
    {
        $keys = [
            'pintu_status',
            'dinding_kanan_status',
            'dinding_kiri_status',
            'dinding_depan_status',
            'atap_status',
            'lantai_status',
        ];

        $failed = 0;
        foreach ($keys as $k) {
            if ($this->$k === false) {
                $failed++;
            }
        }

        return $failed;
    }

    /**
     * Label kesimpulan dalam format human-readable
     */
    public function getKesimpulanLabelAttribute(): string
    {
        return match ($this->kesimpulan) {
            'layak' => 'Layak',
            'layak_dengan_catatan' => 'Layak Dengan Catatan',
            'tidak_layak' => 'Tidak Layak',
            default => ucfirst(str_replace('_', ' ', $this->kesimpulan ?? 'Belum Ditentukan')),
        };
    }
}
