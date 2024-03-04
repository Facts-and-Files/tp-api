<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Score extends Model
{
    const CREATED_AT = 'Timestamp';
    const UPDATED_AT = null;

    protected $table = 'Score';

    protected $primaryKey = 'ScoreId';

    protected $guarded = ['ScoreId'];
}
