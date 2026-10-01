<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Karyawan extends Model
{
    protected $fillable = [
        'nama',
        'nomor_induk',
        'status',
    ];

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }
}
