<?php

namespace App\Models;

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
}
