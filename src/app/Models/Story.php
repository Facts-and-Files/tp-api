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
        'dcterms:created',
        'StoryLanguage'
    ];

    /**
     * append new/renamed fields
     *
     * @var array
     */
    protected $appends = [
        'ItemIds',
        'DcTitle',
        'DcDescription',
        'DcCreator',
        'DcSource',
        'DcContributor',
        'DcPublisher',
        'DcCoverage',
        'DcDate',
        'DcType',
        'DcRelation',
        'DcRights',
        'DcLanguage',
        'DcIdentifier',
        'DctermsMedium',
        'DctermsCreated',
        'DctermsProvenance',
    ];

    /**
     * Get the ItemIds for the story.
     */
    public function getItemIdsAttribute()
    {
        return $this->hasMany(Item::class, 'StoryId')->pluck('ItemId');
    }

    /**
     * Get the DcTitle for the story.
     */
    public function getDcTitleAttribute()
    {
        return $this->attributes['dc:title'];
    }

    /**
     * Get the DcDescription for the story.
     */
    public function getDcDescriptionAttribute()
    {
        return $this->attributes['dc:description'];
    }

    /**
     * Get the DcCreator for the story.
     */
    public function getDcCreatorAttribute()
    {
        return $this->attributes['dc:creator'];
    }

    /**
     * Get the DcSource for the story.
     */
    public function getDcSourceAttribute()
    {
        return $this->attributes['dc:source'];
    }

    /**
     * Get the DcContributor for the story.
     */
    public function getDcContributorAttribute()
    {
        return $this->attributes['dc:contributor'];
    }

    /**
     * Get the DcPublisher for the story.
     */
    public function getDcPublisherAttribute()
    {
        return $this->attributes['dc:publisher'];
    }

    /**
     * Get the DcCoverage for the story.
     */
    public function getDcCoverageAttribute()
    {
        return $this->attributes['dc:coverage'];
    }

    /**
     * Get the dc:date for the story.
     */
    public function getDcDateAttribute()
    {
        return $this->attributes['dc:date'];
    }

    /**
     * Get the dc:type for the story.
     */
    public function getDcTypeAttribute()
    {
        return $this->attributes['dc:type'];
    }

    /**
     * Get the dc:relation for the story.
     */
    public function getDcRelationAttribute()
    {
        return $this->attributes['dc:relation'];
    }

    /**
     * Get the dc:rights for the story.
     */
    public function getDcRightsAttribute()
    {
        return $this->attributes['dc:rights'];
    }

    /**
     * Get the dc:language for the story.
     */
    public function getDcLanguageAttribute()
    {
        return $this->attributes['dc:language'];
    }

    /**
     * Get the dc:identifier for the story.
     */
    public function getDcIdentifierAttribute()
    {
        return $this->attributes['dc:identifier'];
    }

    /**
     * Get the dcterms:medium for the story.
     */
    public function getDctermsMediumAttribute()
    {
        return $this->attributes['dcterms:medium'];
    }

    /**
     * Get the dcterms:created for the story.
     */
    public function getDctermsCreatedAttribute()
    {
        return $this->attributes['dcterms:created'];
    }

    /**
     * Get the dcterms:provenance for the story.
     */
    public function getDctermsProvenanceAttribute()
    {
        return $this->attributes['dcterms:provenance'];
    }
}
