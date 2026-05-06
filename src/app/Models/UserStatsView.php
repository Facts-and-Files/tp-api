<?php

namespace App\Models;

use App\Casts\Ceil;
use Illuminate\Database\Eloquent\Model;

class UserStatsView extends Model
{
    protected $table = 'user_stats_view';

    public const CREATED_AT = null;
    public const UPDATED_AT = null;

    protected $fillable = [];

    protected $primaryKey = 'UserId';

    protected $casts = [
        'UserId' => 'integer',
        'Locations' => 'integer',
        'ManualTranscriptions' => 'integer',
        'Enrichments' => 'integer',
        'Descriptions' => 'integer',
        'HTRTranscriptions' => 'integer',
        'Miles' => Ceil::class,
    ];
}
