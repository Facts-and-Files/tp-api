<?php

namespace App\Models;

use App\Models\Language;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    const CREATED_AT = 'Timestamp';
    const UPDATED_AT = 'LastUpdated';

    protected $table = 'Item';

    protected $primaryKey = 'ItemId';

    protected $guarded = ['StoryId', 'ItemId', 'ProjectItemId'];

    protected $hidden = [
        'CompletionStatusId',
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
        'Properties'
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

    public function persons(): BelongsToMany
    {
        return $this->belongsToMany(Person::class, 'ItemPerson', 'ItemId', 'PersonId');
    }

    public function properties(): BelongsToMany
    {
        return $this->belongsToMany(Property::class, 'ItemProperty', 'ItemId', 'PropertyId');
    }

    public function story()
    {
        return $this->belongsTo(Story::class, 'StoryId');
    }
// to harmonize the API regarding the existent database schema
// we make use some custom accessors and mutators

    public function getDescriptionLangAttribute(): Language
    {
        return $this->language()->first() ?: new Language();
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

        return $status ?: new CompletionStatus();
    }

    public function getTranscriptionAttribute(): Transcription|HtrDataRevision|array
    {
        if ($this->TranscriptionSource === 'manual') {
            $manualTranscription = $this
                ->transcriptions()
                ->firstWhere('CurrentVersion', 1);

            $transcription = $manualTranscription
                ? [
                    'UserId' => $manualTranscription->UserId,
                    'TranscriptionText' => $manualTranscription->TextNoTags,
                    'Text' => $manualTranscription->Text,
                    'CurrentVersion' => $manualTranscription->CurrentVersion,
                    'NoText' => $manualTranscription->NoText,
                    'Language' => $manualTranscription->language,
                ]
                : new Transcription();

            return $transcription;
        }

        if ($this->TranscriptionSource === 'htr') {
            $latest = $this
                ->htrData()
                ->with(['htrDataRevision' => function ($query) {
                    $query->latest();
                }, 'language'])
                ->latest()
                ->first();

            if ($latest && $latest->htrDataRevision->isNotEmpty()) {
                $revision = $latest->htrDataRevision->first();

                return [
                    'UserId' => $revision->UserId,
                    'TranscriptionText' => $revision->TranscriptionText,
                    'Text' => $revision->TranscriptionData,
                    'Language' => $latest->language, // comes from HtrData
                ];
            }

            return [];
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

    public function getPlacesAttribute(): Collection
    {
        return $this->places()->get() ?: [];
    }
}
