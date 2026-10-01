<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class EvidenceItem extends Model
{
    protected $fillable = [
        'shipment_id',
        'sop_photo_point_id',
        'file_path',
        'file_name',
        'tipe_item',
        'captured_at',
        'gps_lat',
        'gps_lng',
        'is_tambahan',
    ];

    protected function casts(): array
    {
        return [
            'captured_at' => 'datetime',
            'is_tambahan' => 'boolean',
            'gps_lat' => 'decimal:8',
            'gps_lng' => 'decimal:8',
        ];
    }

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }

    public function sopPhotoPoint(): BelongsTo
    {
        return $this->belongsTo(SopPhotoPoint::class);
    }

    public function ocrResult(): HasOne
    {
        return $this->hasOne(OcrResult::class);
    }
}
