<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPayment extends Model
{
    use HasFactory;

    public function booking():BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
    public function user():BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
