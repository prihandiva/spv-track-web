<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OcrResult extends Model
{
    protected $fillable = [
        'evidence_item_id',
        'detected_container_no',
        'cocok_dengan_input',
        'confidence_score',
        'orang_terdeteksi',
        'barcode_terdeteksi',
        'barcode_value',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'cocok_dengan_input' => 'boolean',
            'orang_terdeteksi' => 'boolean',
            'barcode_terdeteksi' => 'boolean',
            'processed_at' => 'datetime',
            'confidence_score' => 'decimal:2',
        ];
    }

    public function evidenceItem(): BelongsTo
    {
        return $this->belongsTo(EvidenceItem::class);
    }
}
