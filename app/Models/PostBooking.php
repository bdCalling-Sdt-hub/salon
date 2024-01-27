<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostBooking extends Model
{
    use HasFactory;

    public function services()
    {
        return $this->belongsTo(Service::class);
    }
}
