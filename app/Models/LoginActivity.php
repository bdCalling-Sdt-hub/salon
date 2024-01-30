<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoginActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'browser',
        'device_name',
        'location',
        'login_time',
        'status',
    ];

     public function users():BelongsTo
     {
         return $this->belongsTo(User::class,'user_id');
     }
}
