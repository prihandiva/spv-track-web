<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Shipment extends Model
{
    protected $fillable = [
        'user_id',
        'karyawan_id',
        'shipment_group',
        'shipment_no',
        'plat_nomor',
        'nama_sopir',
        'packing_list_no',
        'tujuan_pengiriman',
        'agen_forwarding',
        'waktu_kedatangan_container',
        'waktu_keberangkatan_container',
        'jenis_produk',
        'jenis_pengiriman',
        'nomor_container_atau_plat',
        'cuaca',
        'waktu',
        'tanggal_staging',
        'warehouse_lokasi',
        'status',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_staging' => 'date',
            'submitted_at' => 'datetime',
            'waktu_kedatangan_container' => 'datetime',
            'waktu_keberangkatan_container' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class);
    }

    public function karyawans(): BelongsToMany
    {
        return $this->belongsToMany(Karyawan::class, 'karyawan_shipment')->withTimestamps();
    }

    /**
     * Get all assigned karyawans, fallback to single karyawan_id relation
     */
    public function allKaryawans()
    {
        if ($this->relationLoaded('karyawans') && $this->karyawans->isNotEmpty()) {
            return $this->karyawans;
        }

        $list = $this->karyawans()->get();
        if ($list->isNotEmpty()) {
            return $list;
        }

        return $this->karyawan ? collect([$this->karyawan]) : collect();
    }

    public function shipmentOrder(): HasOne
    {
        return $this->hasOne(ShipmentOrder::class);
    }

    public function packingList(): HasOne
    {
        return $this->hasOne(PackingList::class);
    }

    public function evidenceItems(): HasMany
    {
        return $this->hasMany(EvidenceItem::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(ShipmentPhoto::class);
    }

    public function timelinePhotos(): HasMany
    {
        return $this->hasMany(ShipmentPhoto::class)->where('is_extra', false)->orderBy('point_no');
    }

    public function extraPhotos(): HasMany
    {
        return $this->hasMany(ShipmentPhoto::class)->where('is_extra', true)->latest();
    }
}
