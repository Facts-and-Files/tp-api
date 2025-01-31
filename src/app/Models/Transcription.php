<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Language;

class Transcription extends Model
{
    const CREATED_AT = 'Timestamp';
    const UPDATED_AT = null;

    protected $table = 'Transcription';

    protected $primaryKey = 'TranscriptionId';

    protected  $casts = [
        'CurrentVersion' => 'boolean',
        'NoText' => 'boolean',
    ];

    protected $guarded = ['CurrentVersion'];

    protected $appends = [
        'Language',
    ];

    public function language(): BelongsToMany
    {
        return $this->belongsToMany(
            Language::class, 'TranscriptionLanguage', 'TranscriptionId', 'LanguageId'
        );
    }

    public function getLanguageAttribute(): Collection
    {
        return $this->language()->get();
    }

}
