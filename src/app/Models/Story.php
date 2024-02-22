<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use App\Models\CompletionStatus;

class Story extends Model
{
    const CREATED_AT = 'Timestamp';
    const UPDATED_AT = 'LastUpdated';

    protected $table = 'Story';

    protected $primaryKey = 'StoryId';

    protected $fillable = [
        'DatasetId',
        'HasHtr',
        'Dc',
        'Public'
    ];

    protected $casts = [
        'HasHtr' => 'boolean',
        'Public' => 'boolean'
    ];

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
        'dcterms:provenance',
        'dc:identifier',
        'OldStoryId',
        'edm:agent',
        'dcterms:created'
    ];

    protected $appends = [
        'ItemIds',
        'Dcterms',
        'Dc',
        'Edm',
        'Place',
        'CompletionStatus'
    ];

// define relations

    public function campaigns(): BelongsToMany
    {
        return $this->belongsToMany(Campaign::class, 'StoryCampaign', 'StoryId', 'CampaignId');
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class, 'StoryId');;
    }

    public function completionStatus(): BelongsTo
    {
        return $this->belongsTo(CompletionStatus::class, 'CompletionStatusId');
    }

// to harmonize the API regarding the existent database schema
// we make use some custom accessors and mutators

    public function getItemIdsAttribute(): Collection
    {
        return $this->items()->pluck('ItemId');
    }

    public function getCompletionStatusAttribute(): CompletionStatus
    {
        $plucked = $this
            ->completionStatus()
            ->first(['CompletionStatusId as StatusId', 'Name', 'ColorCode', 'ColorCodeGradient']);

        return $plucked;
    }

    public function getDctermsAttribute(): array
    {
        return [
            'Medium'     => $this->attributes['dcterms:medium'],
            'Created'    => $this->attributes['dcterms:created'],
            'Provenance' => $this->attributes['dcterms:provenance'],
        ];
    }

    public function getDcAttribute(): array
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

    public function setDcAttribute(array $values): void
    {
        $this->attributes['dc:title'] = $values['Title'] ?? $this->attributes['dc:title'];
    }

    public function getEdmAttribute(): array
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

    public function getPlaceAttribute(): array
    {
        return [
            'Name'      => $this->attributes['PlaceName'],
            'Latitude'  => $this->attributes['PlaceLatitude'],
            'Longitude' => $this->attributes['PlaceLongitude'],
            'Zoom'      => $this->attributes['placeZoom']
        ];
    }
}
