<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\HtrDataRevision;

class HtrData extends Model
{
    /**
     * The table associated with the model.
     *
     * Config over convention here to respect exsting table names.
     *
     * @var string
     */
    protected $table = 'HtrData';

    /**
     * Use user defined exisiting timestamp columns
     */
    const CREATED_AT = 'Timestamp';
    const UPDATED_AT = 'LastUpdated';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'HtrDataId';

    /**
     * The attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'HtrDataId',
        'ItemId',
        'Language'
    ];

    /**
     * append properties
     *
     * @var array
     */
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

    /**
     * Get the last revision of the 1:n transcription relationship
     *
     * @return HasOne
     */
    public function latestRevision(): HasOne
    {
        $latest = $this
            ->hasOne(HtrDataRevision::class, 'HtrDataId')
            ->latestOfMany('LastUpdated');

        return $latest;
    }

    /**
     * define n:n relationship to languages with a pivot table
     *
     * @return BelongsToMany
     */
    public function language(): BelongsToMany
    {
        return $this->belongsToMany(Language::class, 'HtrDataLanguage', 'HtrDataId', 'LanguageId');
    }

// we make usage of some custom accessors and mutators

    /**
     * create Language property/attribute getter
     *
     * @return Collection
     */
    public function getLanguageAttribute(): Collection
    {
        return $this->language()->get();
    }

    /**
     * create TranscriptionData property/attribute getter
     *
     * @return string|null
     */
    public function getTranscriptionDataAttribute(): string|null
    {
        $latestRevision = $this->latestRevision()->first();

        return $latestRevision['TranscriptionData'];
    }

    /**
     * create TranscriptionText property/attribute getter
     *
     * @return string|null
     */
    public function getTranscriptionTextAttribute(): string|null
    {
        $latestRevision = $this->latestRevision()->first();

        return $latestRevision['TranscriptionText'];
    }

    /**
     * create UserId property/attribute getter
     *
     * @return int|null
     */
    public function getUserIdAttribute(): int|null
    {
        $latestRevision = $this->latestRevision()->first();

        return $latestRevision['UserId'];
    }
}
