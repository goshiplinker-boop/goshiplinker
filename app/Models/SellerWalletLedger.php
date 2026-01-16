<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SellerWalletLedger extends Model
{
    protected $table = 'seller_wallet_ledger';

    protected $fillable = [
        'company_id',
        'order_id',
        'shipment_id',
        'courier_id',
        'courier_code',
        'tracking_number',
        'transaction_type',
        'direction',
        'cod_charges',
        'amount',
        'opening_balance',
        'closing_balance',
        'description',
        'source',
    ];

    protected $casts = [
        'amount'           => 'decimal:2',
        'opening_balance'  => 'decimal:2',
        'closing_balance'  => 'decimal:2',
    ];

    /* Relationships */

    public function wallet()
    {
        return $this->belongsTo(SellerWallet::class, 'company_id','company_id');
    }
    public function shipment()
    {
        return $this->belongsTo(ShipmentInfo::class, 'shipment_id');
    }
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function weightDiscrepancy()
    {
        return $this->hasOne(WeightDiscrepancy::class, 'wallet_ledger_id', 'id');
    }

}
