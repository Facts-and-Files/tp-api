<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Item extends Model
{
    const CREATED_AT = 'Timestamp';
    const UPDATED_AT = 'LastUpdated';

    protected $table = 'Item';

    protected $primaryKey = 'ItemId';

    protected $guarded = ['StoryId', 'ItemId'];

    protected $hidden = [
        'CompletionStatusId',
        'ProjectItemId',
        'DatasetId',
        'DescriptionLanguage',
        'Exported',
        'OldItemId',
        'edm:WebResource',
        'EuropeanaAttachment'
    ];

    protected $appends = [
        'DescriptionLang',
        'CompletionStatus',
        'Transcription',
        'Properties',
        'Places',
        'Persons'
    ];

// declare relationships

    public function htrData(): HasMany
    {
        return $this->hasMany(HtrData::class, 'ItemId');
    }

    public function transcriptions(): HasMany
    {
        return $this->hasMany(Transcription::class, 'ItemId');
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'DescriptionLanguage');
    }

    public function completionStatus(): BelongsTo
    {
        return $this->belongsTo(CompletionStatus::class, 'CompletionStatusId');
    }

    public function places(): HasMany
    {
        return $this->hasMany(Place::class, 'ItemId');
    }

    public function persons(): HasMany
    {
       return $this->hasMany(Person::class, 'ItemId');
    }

    public function properties(): HasManyThrough
    {
        return $this->hasManyThrough(
            Property::class,
            ItemProperty::class,
            'ItemId',
            'PropertyId',
            'ItemId',
            'PropertyId');
    }

// to harmonize the API regarding the existent database schema
// we make use some custom accessors and mutators

    public function getDescriptionLangAttribute(): Language|array
    {
        return $this->language()->first() ?: [];
    }

    public function getCompletionStatusAttribute(): CompletionStatus
    {
        $status = $this
            ->completionStatus()
            ->first([
                'CompletionStatusId as StatusId',
                'Name',
                'ColorCode',
                'ColorCodeGradient'
            ]);

        return $status;
    }

    public function getPlacesAttribute(): Collection
    {
        return $this->places()->get() ?: [];
    }

    public function getTranscriptionAttribute(): Transcription|HtrDataRevision|array
    {
        if ($this->TranscriptionSource === 'manual') {
            $manualTranscription = $this
                ->transcriptions()
                ->select('UserId', 'TextNoTags as TranscriptionText', 'CurrentVersion')
                ->firstWhere('CurrentVersion', 1);

            return $manualTranscription ? $manualTranscription : [];
        }

        if ($this->TranscriptionSource === 'htr') {
            $latest = $this
                ->htrData()
                ->with(['htrDataRevision' => function ($query) {
                    $query->latest();
                }])
                ->latest()
                ->first();

            return $latest ? $latest->htrDataRevision->first() : [];
        }

        return [];
    }

    public function getPropertiesAttribute(): Collection
    {
        $properties = $this
            ->properties()
            ->get(['Property.PropertyId', 'Value', 'Description', 'PropertyTypeId']);

        return $properties ?: [];
    }

    public function getPersonsAttribute(): Collection
    {
        return $this->persons()->get() ?: [];
    }
}
