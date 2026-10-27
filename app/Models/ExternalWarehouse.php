<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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
