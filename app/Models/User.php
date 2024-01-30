<?php

namespace App\Models;

use App\Models\ServiceRating;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
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

     public function login_activities()
     {
         return $this->hasMany(LoginActivity::class, 'user_id');
     }

    public function payment(): HasMany
    {
        return $this->hasMany(Payment::class, 'user_id');
    }

    public function subscription(): HasMany
    {
        return $this->hasMany(Subscription::class, 'user_id');
    }


    public function rating()
    {
        return $this->hasMany(ServiceRating::class);
    }

    public function booking(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function provider()
    {
        return $this->hasMany(Provider::class);
    }
}
