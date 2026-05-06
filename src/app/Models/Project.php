<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    public const CREATED_AT = null;
    public const UPDATED_AT = null;

    protected $table = 'Project';

    protected $primaryKey = 'ProjectId';

    public $incrementing = false;

    protected $guarded = [];

    public function stories(): HasMany
    {
        return $this->hasMany(Story::class, 'ProjectId');
    }
}
