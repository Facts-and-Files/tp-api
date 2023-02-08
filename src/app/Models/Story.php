<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Story extends Model
{
    /**
     * Use user defined exisiting timestamp columns
     */
    const CREATED_AT = null; // no Timestamp column available, maybe include later
    const UPDATED_AT = 'LastUpdated';

    /**
     * The table associated with the model.
     *
     * Config over convention here to respect exsting table names.
     *
     * @var string
     */
    protected $table = 'Story';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'StoryId';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];

    /**
     * hide old field names
     *
     * @var array
     */
    protected $hidden = [
        'dc:title',
        'dc:description',
        'edm:landingPage',
        'PlaceName',
        'PlaceLatitude',
        'PlaceLongitude',
        'placeZoom',
        'PlaceLink',
        'PlaceComment',
        'PlaceUserId',
        'PlaceUserGenerated',
        'dc:creator',
        'dc:source',
        'Summary',
        'ParentStory',
        'SearchText',
        'DateStart',
        'DateEnd',
        'OrderIndex',
        'Public',
        'ImportName',
        'edm:country',
        'edm:dataProvider',
        'edm:provider',
        'edm:rights',
        'dc:contributor',
        'edm:year',
        'dc:publisher',
        'dc:coverage',
        'dc:date',
        'dc:type',
        'dc:relation',
        'dcterms:medium',
        'edm:datasetName',
        'edm:begin',
        'edm:end',
        'ProjectId',
        'edm:isShownAt',
        'dc:rights',
        'dc:language',
        'edm:language',
        'CompletionStatusId',
        'DatasetId',
        'dcterms:provenance',
        'dc:identifier',
        'OldStoryId',
        'edm:agent',
        'dcterms:created'
    ];

    /**
     * append new/renamed fields
     *
     * @var array
     */
    protected $appends = [
        'ItemIds',
        'Dcterms',
        'Dc',
        'Edm',
        'Place',
        'CompletionStaus'
    ];

// to harmonize the API regarding the existent database schema
// we make use some custom accessors and mutators

    /**
     * Get the ItemIds for the story.
     */
    public function getItemIdsAttribute()
    {
        return $this->hasMany(Item::class, 'StoryId')->pluck('ItemId');
    }

    /**
     * Get the dcterm object
     */
    public function getDctermsAttribute()
    {
        return [
            'Medium'     => $this->attributes['dcterms:medium'],
            'Created'    => $this->attributes['dcterms:created'],
            'Provenance' => $this->attributes['dcterms:provenance'],
        ];
    }

    /**
     * Get the dc object
     */
    public function getDcAttribute()
    {
        return [
            'Title'       => $this->attributes['dc:title'],
            'Description' => $this->attributes['dc:description'],
            'Creator'     => $this->attributes['dc:creator'],
            'Source'      => $this->attributes['dc:source'],
            'Contributor' => $this->attributes['dc:contributor'],
            'Publisher'   => $this->attributes['dc:publisher'],
            'Coverage'    => $this->attributes['dc:coverage'],
            'Date'        => $this->attributes['dc:date'],
            'Type'        => $this->attributes['dc:type'],
            'Relation'    => $this->attributes['dc:relation'],
            'Rights'      => $this->attributes['dc:rights'],
            'Language'    => $this->attributes['dc:language'],
            'Identifier'  => $this->attributes['dc:identifier']
        ];
    }

    /**
     * Get the edm object for the story.
     */
    public function getEdmAttribute()
    {
        return [
            'LandingPage'  => $this->attributes['edm:landingPage'],
            'Country'      => $this->attributes['edm:country'],
            'DataProvider' => $this->attributes['edm:dataProvider'],
            'Provider'     => $this->attributes['edm:provider'],
            'Rights'       => $this->attributes['edm:rights'],
            'Year'         => $this->attributes['edm:year'],
            'DatasetName'  => $this->attributes['edm:datasetName'],
            'Begin'        => $this->attributes['edm:begin'],
            'End'          => $this->attributes['edm:end'],
            'IsShownAt'    => $this->attributes['edm:isShownAt'],
            'Language'     => $this->attributes['edm:language'],
            'Agent'        => $this->attributes['edm:agent']
        ];
    }

    /**
     * Get the place object of the story
     */
    public function getPlaceAttribute()
    {
        return [
            'Name'      => $this->attributes['PlaceName'],
            'Latitude'  => $this->attributes['PlaceLatitude'],
            'Longitude' => $this->attributes['PlaceLongitude'],
            'Zoom'      => $this->attributes['placeZoom']
        ];
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
}
