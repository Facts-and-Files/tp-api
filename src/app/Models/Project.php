<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    const CREATED_AT = null;
    const UPDATED_AT = null;

    protected $table = 'Project';

    protected $primaryKey = 'ProjectId';

    public $incrementing = false;

    protected $guarded = [];
}
