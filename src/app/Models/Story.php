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
        'ExternalRecordId',
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
        'PreviewImage',
        'DatasetId',
        'dcterms:provenance',
        'dc:identifier',
        'OldStoryId',
        'edm:agent',
        'dcterms:created',
        'StoryLanguage'
    ];

    /**
     * append new/renamed fields
     *
     * @var array
     */
    protected $appends = [
        'ItemIds'
    ];

    /**
     * Get the ItemIds for the story.
     */
    public function getItemIdsAttribute()
    {
        return $this->hasMany(Item::class, 'StoryId')->pluck('ItemId');
    }
}
