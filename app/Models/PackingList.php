<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PackingList extends Model
{
    protected $fillable = [
        'shipment_id',
        'packing_list_no',
        'packing_list_date',
        'reference_no',
        'ref_date',
        'order_no',
        'order_date',
        'contact_person',
        'phone',
        'email',
        'description_produk',
        'hs_code',
        'country_of_origin',
        'raw_ocr_text',
    ];

    protected function casts(): array
    {
        return [
            'packing_list_date' => 'date',
            'ref_date' => 'date',
            'order_date' => 'date',
        ];
    }

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }

    public function bales(): HasMany
    {
        return $this->hasMany(PackingListBale::class);
    }
}
