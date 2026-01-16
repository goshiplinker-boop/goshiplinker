<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class CourierRateCard extends Model
{
    use HasFactory;

     protected $table = 'courier_rate_card';

    protected $fillable = [
        'company_id',
        'courier_id',
        'zone_name',
        'weight_slab_kg',
        'base_freight_forward',
        'additional_freight',
        'rto_freight',
        'cod_charge',
        'cod_percentage',
        'delivery_sla',
        'cod_allowed',
        'sort_order',
        'status',
    ];

    protected $casts = [
        'weight_slab_kg'       => 'decimal:2',
        'base_freight_forward' => 'decimal:2',
        'additional_freight'   => 'decimal:2',
        'rto_freight'          => 'decimal:2',
        'cod_charge'           => 'decimal:2',
        'cod_percentage'       => 'decimal:2',
        'cod_allowed'          => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function courier()
    {
        return $this->belongsTo(Courier::class);
    }
}
