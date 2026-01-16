<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SellerWallet extends Model
{
    protected $table = 'seller_wallets';

    protected $fillable = [
        'company_id',
        'balance',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
    ];

    /* Relationships */

    public function ledgers()
    {
        return $this->hasMany(
            SellerWalletLedger::class,
            'company_id',
            'company_id'
        );
    }
}
