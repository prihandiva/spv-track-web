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
        'nomor_seal',
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
        'warehouse_type',
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

    public function getWarehouseLabelAttribute(): string
    {
        if (($this->warehouse_type ?? 'internal') === 'external') {
            return $this->warehouse_lokasi ? ($this->warehouse_lokasi . ' (Eksternal)') : 'Warehouse Eksternal';
        }
        return 'Gudang ' . ucfirst($this->warehouse_lokasi ?: 'Atas');
    }

    public function getIsWarehouseInternalAttribute(): bool
    {
        return ($this->warehouse_type ?? 'internal') === 'internal';
    }

    /**
     * Waktu awal staging (prioritas: waktu_kedatangan_container, fallback: created_at).
     */
    public function getWaktuAwalStagingAttribute(): ?\Carbon\Carbon
    {
        return $this->waktu_kedatangan_container ?: ($this->created_at ? \Carbon\Carbon::parse($this->created_at) : null);
    }

    /**
     * Waktu akhir staging (prioritas: waktu_keberangkatan_container, fallback: submitted_at jika status submitted).
     */
    public function getWaktuAkhirStagingAttribute(): ?\Carbon\Carbon
    {
        if ($this->waktu_keberangkatan_container) {
            return $this->waktu_keberangkatan_container;
        }
        if ($this->status === 'submitted' && $this->submitted_at) {
            return $this->submitted_at;
        }
        return null;
    }

    /**
     * Total selisih lead time staging dalam menit (waktu akhir staging - waktu awal staging).
     */
    public function getLeadTimeMinutesAttribute(): ?int
    {
        $start = $this->waktu_awal_staging;
        $end = $this->waktu_akhir_staging;

        if (! $start || ! $end) {
            return null;
        }

        $minutes = (int) $start->diffInMinutes($end, false);
        return abs($minutes);
    }

    /**
     * Format lead time staging ke "X jam Y menit" atau "X menit".
     */
    public function getLeadTimeFormattedAttribute(): string
    {
        $minutes = $this->lead_time_minutes;

        if ($minutes === null) {
            return $this->status === 'draft' ? 'Sedang berjalan' : '—';
        }

        return static::formatDurationMinutes($minutes);
    }

    /**
     * Helper statis memformat jumlah menit ke format jam dan menit bahasa Indonesia.
     */
    public static function formatDurationMinutes(?int $minutes): string
    {
        if ($minutes === null) {
            return '—';
        }

        if ($minutes === 0) {
            return '< 1 menit';
        }

        $hours = intdiv($minutes, 60);
        $remMinutes = $minutes % 60;

        if ($hours > 0 && $remMinutes > 0) {
            return "{$hours} jam {$remMinutes} menit";
        }

        if ($hours > 0) {
            return "{$hours} jam";
        }

        return "{$remMinutes} menit";
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

    public function containerInspection(): HasOne
    {
        return $this->hasOne(ContainerInspection::class);
    }

    public function baleInspection(): HasOne
    {
        return $this->hasOne(BaleInspection::class);
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

    public function activities(): HasMany
    {
        return $this->hasMany(ShipmentActivity::class)->latest();
    }

    /**
     * Record an audit trail activity log on this shipment.
     */
    public function logActivity(string $action, string $title, ?string $description = null, ?array $changes = null, ?User $user = null): ShipmentActivity
    {
        $actor = $user ?? auth()->user();
        $name = $actor ? $actor->nama_warehouse : 'Sistem SPV-Track';
        $role = $actor ? $actor->role : 'system';

        return $this->activities()->create([
            'user_id' => $actor?->id,
            'user_name' => $name,
            'user_role' => $role,
            'action' => $action,
            'title' => $title,
            'description' => $description,
            'changes' => $changes,
        ]);
    }
}
