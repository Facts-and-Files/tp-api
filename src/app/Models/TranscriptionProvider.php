<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TranscriptionProvider extends Model
{
    const CREATED_AT = null;
    const UPDATED_AT = null;

    protected $table = 'TranscriptionProvider';

    protected $primaryKey = 'TranscriptionProviderId';

    protected $guarded = ['TranscriptionProviderId'];

    public function htrData(): HasMany
    {
        return $this->hasMany(HtrData::class, 'TranscriptionProviderId');
    }
}
