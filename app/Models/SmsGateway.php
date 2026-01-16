<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsGateway extends Model
{
    protected $fillable = [
        'company_id',
        'gateway_name',
        'http_method',
        'gateway_url',
        'dlt_header_name',
        'dlt_header_id',
        'dlt_template_name',
        'dlt_template_id',
        'mobile',
        'other_parameters',
        'status',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
