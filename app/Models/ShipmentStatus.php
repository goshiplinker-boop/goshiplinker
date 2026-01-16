<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShipmentStatus extends Model
{
    use HasFactory;
    protected $fillable = ['parent_code', 'code', 'name', 'status_colour','status']; 
}
