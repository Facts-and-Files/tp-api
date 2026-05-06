<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Score extends Model
{
    public const CREATED_AT = 'Timestamp';
    public const UPDATED_AT = null;

    protected $table = 'Score';

    protected $primaryKey = 'ScoreId';

    protected $guarded = ['ScoreId'];

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'TeamScore', 'ScoreId', 'TeamId');
    }
}
