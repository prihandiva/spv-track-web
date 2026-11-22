<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvidenceItem extends Model
{
    protected $fillable = [
        'shipment_id',
        'sop_photo_point_id',
        'file_path',
        'file_name',
        'sha256',
        'size',
        'tipe_item',
        'captured_at',
        'gps_lat',
        'gps_lng',
        'is_tambahan',
        'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'captured_at' => 'datetime',
            'is_tambahan' => 'boolean',
            'gps_lat' => 'decimal:8',
            'gps_lng' => 'decimal:8',
            'size' => 'integer',
        ];
    }

    protected $appends = [
        'file_url',
        'api_file_url',
    ];

    public function getFileUrlAttribute(): string
    {
        if ($this->shipment_id) {
            return route('shipments.evidence.show', [$this->shipment_id, $this->id]);
        }

        return asset('storage/'.$this->file_path);
    }

    public function getApiFileUrlAttribute(): string
    {
        return url('api/evidence/'.$this->id.'/file');
    }

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }

    public function sopPhotoPoint(): BelongsTo
    {
        return $this->belongsTo(SopPhotoPoint::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
