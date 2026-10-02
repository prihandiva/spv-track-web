<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use RuntimeException;

class ShipmentPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipment_id',
        'point_no',
        'point_label',
        'is_extra',
        'path',
        'stamped_path',
        'thumbnail_path',
        'mime',
        'size',
        'sha256',
        'captured_at',
        'stamped_at',
        'uploaded_by',
        'ocr_text',
    ];

    protected function casts(): array
    {
        return [
            'is_extra' => 'boolean',
            'point_no' => 'integer',
            'size' => 'integer',
            'captured_at' => 'datetime',
            'stamped_at' => 'datetime',
        ];
    }

    /**
     * Immutability enforcement:
     * Foto bukti yang sudah tersimpan permanen tidak boleh diubah atau dihapus.
     */
    protected static function booted(): void
    {
        static::updating(function ($photo) {
            throw new RuntimeException('Data foto bukti shipment bersifat immutable (tidak boleh diubah setelah tersimpan permanen).');
        });

        static::deleting(function ($photo) {
            throw new RuntimeException('Data foto bukti shipment bersifat immutable (tidak boleh dihapus setelah tersimpan permanen).');
        });
    }

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getOriginalUrlAttribute(): string
    {
        return route('photos.file', ['photo' => $this->id, 'type' => 'original']);
    }

    public function getStampedUrlAttribute(): string
    {
        return route('photos.file', ['photo' => $this->id, 'type' => 'stamped']);
    }

    public function getThumbnailUrlAttribute(): string
    {
        return route('photos.file', ['photo' => $this->id, 'type' => 'thumbnail']);
    }
}
