<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlaceCombinedDetails extends Model
{
    protected $table = 'place_combined_details_view';

    public $incrementing = false;

    public $timestamps = false;

    protected $primaryKey = null;

    protected $guarded = [];

    protected $casts = [
        'UserGenerated' => 'boolean'
    ];
}
