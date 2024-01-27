<?php

namespace App\Models;

use App\Models\CatCatalogue;
use App\Models\Provider;
use App\Models\ServiceRating;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'provider_id',
        'service_name',
        'service_description',
        'gallary_photo',
        'service_duration',
        'salon_service_charge',
        'home_service_charge',
        'set_booking_mony',
        'available_service_our',
    ];

    public function ServiceRating()
    {
        return $this->hasMany(ServiceRating::class);
    }

    public function salonDetails()
    {
        return $this->hasMany(Catalogue::class);
    }

    public function postbooking()
    {
        return $this->hasMany(PostBooking::class);
    }
}
