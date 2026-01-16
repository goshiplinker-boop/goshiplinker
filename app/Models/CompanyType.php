<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class CompanyType extends Model
{
    use HasFactory;
    protected $table = 'company_types';

    protected $fillable = [
        'type',
        'subtype',
        'status',
    ];
}
