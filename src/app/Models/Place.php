<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Place extends Model
{
    public const CREATED_AT = null;
    public const UPDATED_AT = null;

    protected $table = 'Place';

    protected $primaryKey = 'PlaceId';

    protected $guarded = ['PlaceId'];

    protected $fillable = [
        'Name',
        'Latitude',
        'Longitude',
        'ItemId',
        'Zoom',
        'Comment',
        'UserGenerated',
        'UserId',
        'WikidataName',
        'WikidataId',
        'PlaceRole',
    ];

    protected $casts = [
        'UserGenerated' => 'boolean',
    ];


    protected $hidden = ['Link', 'links'];

    protected $appends = ['Links'];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'ItemId');
    }

    public function links(): HasMany
    {
        return $this->hasMany(PlaceLink::class, 'PlaceId', 'PlaceId');
    }

    public function getLinksAttribute(): Collection
    {
        if ($this->relationLoaded('links')) {
            return $this->getRelation('links');
        }

        return $this->links()->get();
    }
}
