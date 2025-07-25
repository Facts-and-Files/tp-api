<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Place extends Model
{
    const CREATED_AT = null;
    const UPDATED_AT = null;

    protected $table = 'Place';

    protected $primaryKey = 'PlaceId';

    protected $guarded = ['PlaceId'];

    protected $casts = [
        'UserGenerated' => 'boolean'
    ];

    public function item()
    {
        return $this->belongsTo(Item::class, 'ItemId');
    }
}
