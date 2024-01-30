<?php

namespace App\Models;

use App\Models\Catalogue;
use App\Models\Provider;
use App\Models\Users;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Booking extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'user_id',
        'provider_id',
        'service_id',
        'service',
        'price',
        'date',
        'time',
        'status',
        'service_type',
        'service_duration',
        'catalogue_id'
    ];

    public function userPayment(): BelongsTo
    {
        return $this->belongsTo(UserPayment::class);
    }

    public function Provider(): BelongsTo
    {
        return $this->belongsTo(provider::class);
    }

    public function services()
    {
        return $this->belongsTo(Service::class);
    }
}
