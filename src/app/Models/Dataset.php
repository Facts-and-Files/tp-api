<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Dataset extends Model
{
    public const CREATED_AT = null;
    public const UPDATED_AT = null;

    protected $table = 'Dataset';

    protected $primaryKey = 'DatasetId';

    protected $guarded = ['DatasetId'];

    // define relations

    public function stories(): HasMany
    {
        return $this->hasMany(Story::class, 'DatasetId', 'DatasetId');
    }
}
