<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SummaryStatsViewByYear extends Model
{
    protected $table = 'summary_stats_view_by_year';

    const CREATED_AT = null;
    const UPDATED_AT = null;

    protected $fillable = [];

    protected $casts = [
        'Year'                    => 'integer',
        'Month'                   => 'integer',
        'ScoreTypeId'             => 'integer',
        'UniqueUsersPerScoreType' => 'integer',
        'UniqueItemsPerScoreType' => 'integer',
        'OverallUniqueUsers'      => 'integer',
        'OverallUniqueItems'      => 'integer',
        'Amount'                  => 'integer'
    ];
}
