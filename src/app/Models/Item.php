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
        'Transcription',
        'Properties',
        'EditStart',
        'Places',
        'Persons'
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
    public function getTranscriptionAttribute()
    {
        if ($this->TranscriptionSource === 'manual') {
            $manualTranscription = $this
                ->hasMany(Transcription::class, 'ItemId')
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

    /** 
     * Get the timestamp od first transcription
     */
    public function getEditStartAttribute()
    {
        if ($this->TranscriptionSource === 'manual') {
            $dateStart = $this
                ->hasMany(Transcription::class, 'ItemId')
                ->orderBy('Timestamp', 'asc')
                ->pluck('Timestamp')
                ->first();
            
            return $dateStart ? $dateStart : '';
        }

        if($this->TranscriptionSource === 'htr') {
            $dateStart = $this
                ->hasMany(HtrData::class, 'ItemId')
                ->orderBy('Timestamp', 'asc')
                ->pluck('Timestamp')
                ->first();
            
            return $dateStart ? $dateStart : '';
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

    /**
     * Get the Item places
     */
    public function getPlacesAttribute()
    {
        $places = $this
            ->hasMany(Place::class, 'ItemId')
            ->get();

        return $places ? $places : [];
    }

    /**
     * Get the Item persons
     */
    public function getPersonsAttribute()
    {
        $persons = $this
            ->hasMany(Person::class, 'ItemId')
            ->get();

        return $persons ? $persons : [];
    }
}
