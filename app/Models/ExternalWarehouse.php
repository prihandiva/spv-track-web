<?php

namespace App\Models;

use App\Helpers\FormatHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExternalWarehouse extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'kode',
        'alamat',
        'pic_nama',
        'pic_kontak',
        'status',
        'catatan',
    ];

    protected function nama(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? FormatHelper::toUpper($value) : $value,
            set: fn (?string $value) => $value ? FormatHelper::toUpper($value) : $value,
        );
    }

    protected function alamat(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? FormatHelper::toUpper($value) : $value,
            set: fn (?string $value) => $value ? FormatHelper::toUpper($value) : $value,
        );
    }

    protected function picNama(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? FormatHelper::toUpper($value) : $value,
            set: fn (?string $value) => $value ? FormatHelper::toUpper($value) : $value,
        );
    }

    protected function catatan(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? FormatHelper::toUpper($value) : $value,
            set: fn (?string $value) => $value ? FormatHelper::toUpper($value) : $value,
        );
    }

    /**
     * Scope query to only include active external warehouses.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'aktif');
    }

    /**
     * Get the shipments associated with this warehouse by location name.
     */
    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class, 'warehouse_lokasi', 'nama');
    }
}
