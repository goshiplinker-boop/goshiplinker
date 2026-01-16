<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\ApiCredential;
class User extends Authenticatable
{
    use HasFactory, Notifiable,HasApiTokens;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'company_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role_id' => 'integer',
            'company_id' => 'integer',
        ];
    }





    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    public function leadActivities()
    {
        return $this->hasMany(LeadActivity::class, 'user_id');  // assuming user_id in lead activities
    }

    public function leadStatus()
    {
        return $this->belongsToThrough(LeadStatus::class, Company::class);
    }

    public function country()
    {
        return $this->belongsToThrough(Country::class, Company::class);
    }
    public function apiCredential()
    {
        return $this->hasOne(ApiCredential::class);
    }

}