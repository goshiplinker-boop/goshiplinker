<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourierWeightUpload extends Model
{
    protected $fillable = [
        'courier_id',
        'file_path',
        'status',
        'total_rows',
        'processed_rows',
        'error',
    ];
}
