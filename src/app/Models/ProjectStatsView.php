<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectStatsView extends Model
{
    protected $table = 'project_stats_view';

    public $incrementing = false;
    public $timestamps   = false;

    protected $guarded = [];
}
