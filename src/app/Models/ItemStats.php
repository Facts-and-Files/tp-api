<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemStats extends Model
{
    const CREATED_AT = 'Timestamp';
    const UPDATED_AT = 'LastUpdated';

    protected $table = 'ItemStats';

    protected $guarded = ['StoryId', 'ItemId'];

    protected $primaryKey = 'ItemId';

    protected $casts = [
        'UserIds' => 'json',
        'Enrichments' => 'json'
    ];
}
