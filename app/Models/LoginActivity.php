<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function users()
    {
        return $this->belongsTo(User::class,'user_id');
    }
}
