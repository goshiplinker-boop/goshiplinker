<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsDltTemplate extends Model
{
    protected $fillable = [
        'company_id',
        'template_registration_id',
        'order_status',
        'message_content',
        'status',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
