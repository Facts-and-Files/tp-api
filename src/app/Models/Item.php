<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\hasOneThrough;

class Item extends Model
{
    /**
     * Use user defined exisiting timestamp columns
     */
    const CREATED_AT = 'Timestamp';
    const UPDATED_AT = 'LastUpdated';

    /**
     * The table associated with the model.
     *
     * Config over convention here to respect exsting table names.
     *
     * @var string
     */
    protected $table = 'Item';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'ItemId';

    /**
     * The attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = ['StoryId', 'ItemId'];

    /**
     * hide old field names
     *
     * @var array
     */
    protected $hidden = [
        'CompletionStatusId',
        'ProjectItemId',
        'DatasetId',
        'Manifest',
        'DescriptionLanguage',
        'Exported',
        'OldItemId',
        'edm:WebResource',
        'EuropeanaAttachment'
    ];

    /**
     * append new/renamed fields
     *
     * @var array
     */
    protected $appends = [
        'DescriptionLang',
        'CompletionStatus',
        'TranscriptionText',
        'Properties'
    ];

    public function htrData()
    {
        return $this->hasMany(HtrData::class, 'ItemId');
    }

// to harmonize the API regarding the existent database schema
// we make use some custom accessors and mutators

    /**
     * Get the description language
     */
    public function getDescriptionLangAttribute()
    {
        $plucked = $this
            ->belongsTo(Language::class, 'DescriptionLanguage')
            ->first();

        return $plucked;
    }

    /**
     * Get the completion object of the story
     */
    public function getCompletionStatusAttribute()
    {
        $plucked = $this
            ->belongsTo(CompletionStatus::class, 'CompletionStatusId')
            ->first(['CompletionStatusId as StatusId', 'Name', 'ColorCode', 'ColorCodeGradient']);

        return $plucked;
    }

    /**
     * Get the current version of Item Transcription
     */
    public function getTranscriptionTextAttribute()
    {
        if ($this->TranscriptionSource === 'manual') {
            $manualTranscription = $this
                ->hasMany(Transcription::class, 'ItemId')
                ->firstWhere('CurrentVersion', 1);

            $manualTranscriptionText = $manualTranscription ? $manualTranscription->TextNoTags : '';

            return $manualTranscriptionText;
        }

        if ($this->TranscriptionSource === 'htr') {
            $htrTranscription = $this
                ->htrData()
                ->with(['htrDataRevision' => function ($query) {
                    $query->latest();
                }])
                ->latest()
                ->first()
                ->htrDataRevision
                ->pluck('TranscriptionText')
                ->first();

            return $htrTranscription;
        }

        return '';
    }

    /**
     * Get the Item properties
     */
    public function getPropertiesAttribute()
    {
        $plucked = $this
            ->hasManyThrough(
                Property::class,
                ItemProperty::class,
                'ItemId',
                'PropertyId',
                'ItemId',
                'PropertyId')
            ->get(['Property.PropertyId', 'Value', 'Description', 'PropertyTypeId']);

        return $plucked;
    }
}
