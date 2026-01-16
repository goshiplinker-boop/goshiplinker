<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BuyerInvoiceSetting extends Model
{
    use HasFactory;

    // Define the table name
    protected $table = 'buyer_invoice_settings';

    // Define the fillable attributes
    protected $fillable = [
        'number_type',
        'prefix',
        'start_from',
        'invoice_type',
        'company_id',
    ];
}
