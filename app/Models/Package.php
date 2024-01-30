<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;

//    protected $casts = [
//        'package_features' => 'json',
//    ];
    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}
