<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BaleInspectionItem extends Model
{
    protected $fillable = [
        'bale_inspection_id',
        'item_index',
        'jenis_ketidaksesuaian',
        'ada_ketidaksesuaian',
        'jumlah_bale',
        'tindakan_perbaikan',
        'tindakan_lainnya',
        'nomor_bale_awal',
        'nomor_bale_pengganti',
    ];

    protected function casts(): array
    {
        return [
            'item_index' => 'integer',
            'ada_ketidaksesuaian' => 'boolean',
            'jumlah_bale' => 'integer',
        ];
    }

    public function baleInspection(): BelongsTo
    {
        return $this->belongsTo(BaleInspection::class);
    }

    /**
     * Get pairs of [awal, pengganti] as an array
     *
     * @return array<int, array{awal: string, pengganti: string}>
     */
    public function getBalePairsAttribute(): array
    {
        $rawAwal = trim((string) $this->nomor_bale_awal);
        $rawPengganti = trim((string) $this->nomor_bale_pengganti);

        $awalList = $rawAwal !== '' ? array_values(array_map('trim', explode(',', $rawAwal))) : [];
        $penggantiList = $rawPengganti !== '' ? array_values(array_map('trim', explode(',', $rawPengganti))) : [];

        $count = max(count($awalList), count($penggantiList));
        if ($count === 0) {
            return [['awal' => '', 'pengganti' => '']];
        }

        $pairs = [];
        for ($i = 0; $i < $count; $i++) {
            $pairs[] = [
                'awal' => $awalList[$i] ?? '',
                'pengganti' => $penggantiList[$i] ?? '',
            ];
        }

        return $pairs;
    }

    /**
     * Standard 8 jenis ketidaksesuaian default
     */
    public static function defaultChecklist(): array
    {
        return [
            1 => 'Sobek',
            2 => 'Ada tambalan',
            3 => 'Kotor / Bernoda',
            4 => 'Putus tali strapping',
            5 => 'Sobek pada welding packaging (NW)',
            6 => 'Ada serangga',
            7 => 'Basah',
            8 => 'Label rusak / tidak seragam (EV / Non-EV)',
        ];
    }
}
