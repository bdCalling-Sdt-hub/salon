<?php

namespace App\Models;

use App\Models\Catalogue;
use App\Models\Provider;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceRating extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'service_id',
        'review',
        'rating'
    ];

    public function Service()
    {
        return $this->belongsTo(Service::class);
    }

    public function providerService()
    {
        return $this->belongsTo(Provider::class);
    }

    public function catalougeRating()
    {
        return $this->belongsTo(Catalogue::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function salon(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }
}
