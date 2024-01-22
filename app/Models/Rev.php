<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rev extends Model
{
    use HasFactory;

    public function user() :BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ser():BelongsTo
    {
        return $this->belongsTo(Ser::class);
    }
}
