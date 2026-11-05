<?php

namespace App\Models;

use App\Helpers\FormatHelper;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Karyawan extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'nomor_induk',
        'position',
        'status',
    ];

    protected function nama(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? FormatHelper::toUpper($value) : $value,
            set: fn (?string $value) => $value ? FormatHelper::toUpper($value) : $value,
        );
    }

    protected function nomorInduk(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? FormatHelper::toUpper($value) : $value,
            set: fn (?string $value) => $value ? FormatHelper::toUpper($value) : $value,
        );
    }

    public function getPositionLabelAttribute(): string
    {
        return match (strtolower($this->position ?? 'operator')) {
            'checker' => 'Checker',
            'helper' => 'Helper',
            'inspektor' => 'Inspektor',
            'supervisor' => 'Supervisor',
            default => 'Operator',
        };
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }

    public function multipleShipments(): BelongsToMany
    {
        return $this->belongsToMany(Shipment::class, 'karyawan_shipment')->withTimestamps();
    }
}
