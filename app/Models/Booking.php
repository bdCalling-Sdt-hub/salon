<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
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
    ];
}
