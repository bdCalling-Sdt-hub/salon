<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'image',
        'phone_number',
        'address',
        'user_type',
        'google_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

//    public function login_activities()
//    {
//        return $this->hasMany(LoginActivity::class, 'user_id');
//    }

    public function payment() :HasMany
    {
        return $this->hasMany(Payment::class,'user_id');
    }

    public function subscription() :HasMany
    {
        return $this->hasMany(Subscription::class,'user_id');
    }

    public function rev() :HasMany
    {
        return $this->hasMany(Rev::class);
    }
}
