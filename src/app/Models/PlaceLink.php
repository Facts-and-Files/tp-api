<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlaceLink extends Model
{
    protected $table = 'PlaceLink';
    protected $primaryKey = 'PlaceLinkId';

    protected $fillable = [
        'PlaceId',
        'Provider',
        'Url',
    ];

    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class, 'PlaceId', 'PlaceId');
    }
}
