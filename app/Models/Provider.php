<?php

namespace App\Models;

use App\Model\Category;
use App\Models\Service;
use App\Models\ServiceRating;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Provider extends Model
{
    use HasFactory;

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
    ];

    public function salonDetails()
    {
        return $this->hasMany(Service::class);
    }

    public function providerRating()
    {
        return $this->hasMany(ServiceRating::class);
    }
}
