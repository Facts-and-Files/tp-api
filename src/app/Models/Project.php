<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    const CREATED_AT = null;
    const UPDATED_AT = null;

    protected $table = 'Project';

    protected $primaryKey = 'ProjectId';

    public $incrementing = false;

    protected $guarded = [];

    public function stories(): HasMany
    {
        return $this->hasMany(Story::class, 'ProjectId');
    }
}
