<?php

namespace App\Models;

use App\Model\Category;
use App\Models\Service;
use App\Models\ServiceRating;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Provider extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'category_id',
        'business_name',
        'address',
        'description',
        'available_service_our',
        'cover_photo',
        'gallary_photo',
        'latitude',
        'longitude',
        'provider_id'
    ];

    public function salonDetails()
    {
        return $this->hasMany(Service::class);
    }

    public function Catalouge()
    {
        return $this->hasMany(Catalogue::class);
    }

    public function providerRating()
    {
        return $this->hasMany(ServiceRating::class);
    }

    public function service()
    {
        return $this->hasMany(Service::class);
    }

    // Thre table connect database //

    public function serviceCatelouge()
    {
        return $this->hasManyThrough(Catalogue::class, Service::class);
    }

    public function services()
    {
        return $this->hasMany(Service::class);
    }
}
