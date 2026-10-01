<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SopPhotoPoint extends Model
{
    protected $fillable = [
        'jenis_pengiriman',
        'urutan',
        'nama_titik',
        'deskripsi',
        'tipe_item',
        'wajib',
        'perlu_ocr_container',
        'perlu_deteksi_orang',
        'perlu_deteksi_barcode',
    ];

    protected function casts(): array
    {
        return [
            'wajib' => 'boolean',
            'perlu_ocr_container' => 'boolean',
            'perlu_deteksi_orang' => 'boolean',
            'perlu_deteksi_barcode' => 'boolean',
        ];
    }

    public function evidenceItems(): HasMany
    {
        return $this->hasMany(EvidenceItem::class);
    }
}
