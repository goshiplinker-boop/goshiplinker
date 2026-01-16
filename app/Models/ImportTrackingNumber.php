<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportTrackingNumber extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'import_tracking_numbers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'courier_id',
        'company_id',
        'tracking_number',
        'payment_type',
        'used',
    ];

    /**
     * Relationships
     */
    
    // Relation to the Courier model
    public function courier()
    {
        return $this->belongsTo(Courier::class);
    }

    // Relation to the Company model
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
