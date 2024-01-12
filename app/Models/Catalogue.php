<?php

namespace App\Models;

use App\Models\ServiceRating;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Catalogue extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_id',
        'service_id',
        'catalog_name',
        'catalog_description',
        'image',
        'service_duration',
        'salon_service_charge',
        'home_service_charge',
        'booking_money',
        'service_hour',
    ];

    public function catalouges()
    {
        return $this->hasMany(ServiceRating::class);
    }
}
