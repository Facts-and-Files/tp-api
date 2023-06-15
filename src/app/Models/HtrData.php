<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\HtrDataRevision;
use App\Models\Language;

class HtrData extends Model
{
    protected $table = 'HtrData';

    const CREATED_AT = 'Timestamp';
    const UPDATED_AT = 'LastUpdated';

    protected $primaryKey = 'HtrDataId';

    protected $guarded = [
        'HtrDataId',
        'ItemId',
        'Language'
    ];

    protected $appends = [
        'UserId',
        'TranscriptionData',
        'TranscriptionText',
        'Language'
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'ItemId');
    }

    public function htrDataRevision(): HasMany
    {
        return $this->hasMany(HtrDataRevision::class, 'HtrDataId');
    }

    public function latestRevision(): HasOne
    {
        $latest = $this
            ->hasOne(HtrDataRevision::class, 'HtrDataId')
            ->latestOfMany('LastUpdated');

        return $latest;
    }

    public function language(): BelongsToMany
    {
        return $this->belongsToMany(Language::class, 'HtrDataLanguage', 'HtrDataId', 'LanguageId');
    }

// we make usage of some custom accessors and mutators

    public function getLanguageAttribute(): Collection
    {
        return $this->language()->get();
    }

    public function getTranscriptionDataAttribute(): string|null
    {
        $latestRevision = $this->latestRevision()->first();

        return $latestRevision ? $latestRevision['TranscriptionData'] : null;
    }

    public function getTranscriptionTextAttribute(): string|null
    {
        $latestRevision = $this->latestRevision()->first();

        return $latestRevision ? $latestRevision['TranscriptionText'] : null;
    }

    public function getUserIdAttribute(): int|null
    {
        $latestRevision = $this->latestRevision()->first();

        return $latestRevision ? $latestRevision['UserId'] : null;
    }
}
