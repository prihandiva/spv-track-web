<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackingListBale extends Model
{
    protected $fillable = [
        'packing_list_id',
        'bale_no',
        'gross_kg',
        'net_kg',
        'cond_kg',
    ];

    public function packingList(): BelongsTo
    {
        return $this->belongsTo(PackingList::class);
    }
}
