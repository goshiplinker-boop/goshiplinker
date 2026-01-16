<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WixSite extends Model
{
    protected $fillable = [
        'instance_id',
        'site_name',
        'access_token',
        'token_expires_at',
    ];

    protected $casts = [
        'token_expires_at' => 'datetime',
    ];

    // Determine if token is still fresh
    public function tokenValid(): bool
    {
        return $this->access_token && $this->token_expires_at?->isFuture();
    }
}
