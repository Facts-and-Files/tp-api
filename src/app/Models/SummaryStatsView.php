<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SummaryStatsView extends Model
{
    protected $table = 'summary_stats_view';

    const CREATED_AT = null;
    const UPDATED_AT = null;

    protected $fillable = [];

    protected $casts = [
        'Year'                    => 'integer',
        'Month'                   => 'integer',
        'ScoreTypeId'             => 'integer',
        'UniqueUsersPerScoreType' => 'integer',
        'OverallUniqueUsers'      => 'integer',
        'Amount'                  => 'integer'
    ];
}
