<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShipmentOrder extends Model
{
    protected $fillable = [
        'shipment_id',
        'shipment_group',
        'shipment_no',
        'nama_customer',
        'alamat_customer',
        'forwarding_agent',
        'package_start_loading',
        'end_of_shipment',
        'freight_rate',
        'raw_ocr_text',
    ];

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }
}
