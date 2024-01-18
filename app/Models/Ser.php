<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ser extends Model
{
    use HasFactory;

    public function sal() :BelongsTo
    {
        return $this->belongsTo(Sal::class);
    }
    public function rev():HasMany
    {
        return $this->hasMany(Rev::class);
    }
}
