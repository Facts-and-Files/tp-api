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

    protected $guarded = [
        'StoryId',
        'ExternalRecordId',
        'RecordId',
        'ImportName'
    ];

    protected $casts = [
        'HasHtr' => 'boolean',
        'Public' => 'boolean'
    ];

    protected $hidden = [
        'edm:landingPage',
        'edm:country',
        'edm:dataProvider',
        'edm:provider',
        'edm:rights',
        'edm:year',
        'edm:datasetName',
        'edm:begin',
        'edm:end',
        'edm:isShownAt',
        'edm:language',
        'edm:agent',
        'dc:rights',
        'dc:title',
        'dc:description',
        'dc:creator',
        'dc:source',
        'dc:contributor',
        'dc:publisher',
        'dc:coverage',
        'dc:date',
        'dc:type',
        'dc:relation',
        'dc:language',
        'dc:identifier',
        'dcterms:medium',
        'dcterms:provenance',
        'dcterms:created',
        'PlaceUserId',
        'PlaceUserGenerated',
        'Summary',
        'ParentStory',
        'SearchText',
        'DateStart',
        'DateEnd',
        'OrderIndex',
        'ImportName',
        'CompletionStatusId',
        'OldStoryId'
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

        // fallback to a default CompletionStatus instance if none found
        if ($plucked === null) {
            $plucked = CompletionStatus::find(1);
        }

        return $plucked;
    }

    public function getDctermsAttribute(): array
    {
        return [
            'Medium'     => $this->attributes['dcterms:medium']     ?? null,
            'Created'    => $this->attributes['dcterms:created']    ?? null,
            'Provenance' => $this->attributes['dcterms:provenance'] ?? null
        ];
    }

    public function setDctermsAttribute(array $values): void
    {
        $this->attributes['dcterms:medium']     = $values['Medium']     ?? $this->attributes['dcterms:medium']     ?? null;
        $this->attributes['dcterms:created']    = $values['Created']    ?? $this->attributes['dcterms:created']    ?? null;
        $this->attributes['dcterms:provenance'] = $values['Provenance'] ?? $this->attributes['dcterms:provenance'] ?? null;
    }

    public function getDcAttribute(): array
    {
        return [
            'Title'       => $this->attributes['dc:title']       ?? null,
            'Description' => $this->attributes['dc:description'] ?? null,
            'Creator'     => $this->attributes['dc:creator']     ?? null,
            'Source'      => $this->attributes['dc:source']      ?? null,
            'Contributor' => $this->attributes['dc:contributor'] ?? null,
            'Publisher'   => $this->attributes['dc:publisher']   ?? null,
            'Coverage'    => $this->attributes['dc:coverage']    ?? null,
            'Date'        => $this->attributes['dc:date']        ?? null,
            'Type'        => $this->attributes['dc:type']        ?? null,
            'Relation'    => $this->attributes['dc:relation']    ?? null,
            'Rights'      => $this->attributes['dc:rights']      ?? null,
            'Language'    => $this->attributes['dc:language']    ?? null,
            'Identifier'  => $this->attributes['dc:identifier']  ?? null
        ];
    }

    public function setDcAttribute(array $values): void
    {
        $this->attributes['dc:title']       = $values['Title']       ?? $this->attributes['dc:title']       ?? null;
        $this->attributes['dc:description'] = $values['Description'] ?? $this->attributes['dc:description'] ?? null;
        $this->attributes['dc:creator']     = $values['Creator']     ?? $this->attributes['dc:creator']     ?? null;
        $this->attributes['dc:source']      = $values['Source']      ?? $this->attributes['dc:source']      ?? null;
        $this->attributes['dc:contributor'] = $values['Contributor'] ?? $this->attributes['dc:contributor'] ?? null;
        $this->attributes['dc:publisher']   = $values['Publisher']   ?? $this->attributes['dc:publisher']   ?? null;
        $this->attributes['dc:coverage']    = $values['Coverage']    ?? $this->attributes['dc:coverage']    ?? null;
        $this->attributes['dc:date']        = $values['Date']        ?? $this->attributes['dc:date']        ?? null;
        $this->attributes['dc:type']        = $values['Type']        ?? $this->attributes['dc:type']        ?? null;
        $this->attributes['dc:relation']    = $values['Relation']    ?? $this->attributes['dc:relation']    ?? null;
        $this->attributes['dc:rights']      = $values['Rights']      ?? $this->attributes['dc:rights']      ?? null;
        $this->attributes['dc:language']    = $values['Language']    ?? $this->attributes['dc:language']    ?? null;
        $this->attributes['dc:identifier']  = $values['Identifier']  ?? $this->attributes['dc:identifier']  ?? null;
    }

    public function getEdmAttribute(): array
    {
        return [
            'LandingPage'  => $this->attributes['edm:landingPage']  ?? null,
            'Country'      => $this->attributes['edm:country']      ?? null,
            'DataProvider' => $this->attributes['edm:dataProvider'] ?? null,
            'Provider'     => $this->attributes['edm:provider']     ?? null,
            'Rights'       => $this->attributes['edm:rights']       ?? null,
            'Year'         => $this->attributes['edm:year']         ?? null,
            'DatasetName'  => $this->attributes['edm:datasetName']  ?? null,
            'Begin'        => $this->attributes['edm:begin']        ?? null,
            'End'          => $this->attributes['edm:end']          ?? null,
            'IsShownAt'    => $this->attributes['edm:isShownAt']    ?? null,
            'Language'     => $this->attributes['edm:language']     ?? null,
            'Agent'        => $this->attributes['edm:agent']        ?? null
        ];
    }

    public function setEdmAttribute(array $values): void
    {
        $this->attributes['edm:landingPage']  = $values['LandingPage']  ?? $this->attributes['edm:landingPage']  ?? null;
        $this->attributes['edm:country']      = $values['Country']      ?? $this->attributes['edm:country']      ?? null;
        $this->attributes['edm:dataProvider'] = $values['DataProvider'] ?? $this->attributes['edm:dataProvider'] ?? null;
        $this->attributes['edm:provider']     = $values['Provider']     ?? $this->attributes['edm:provider']     ?? null;
        $this->attributes['edm:rights']       = $values['Rights']       ?? $this->attributes['edm:rights']       ?? null;
        $this->attributes['edm:year']         = $values['Year']         ?? $this->attributes['edm:year']         ?? null;
        $this->attributes['edm:datasetName']  = $values['DatasetName']  ?? $this->attributes['edm:datasetName']  ?? null;
        $this->attributes['edm:begin']        = $values['Begin']        ?? $this->attributes['edm:begin']        ?? null;
        $this->attributes['edm:end']          = $values['End']          ?? $this->attributes['edm:end']          ?? null;
        $this->attributes['edm:isShownAt']    = $values['IsShownAt']    ?? $this->attributes['edm:isShownAt']    ?? null;
        $this->attributes['edm:language']     = $values['Language']     ?? $this->attributes['edm:language']     ?? null;
        $this->attributes['edm:agent']        = $values['Agent']        ?? $this->attributes['edm:agent']        ?? null;
    }

    public function getPlaceAttribute(): array
    {
        return [
            'Name'      => $this->attributes['PlaceName']      ?? null,
            'Latitude'  => $this->attributes['PlaceLatitude']  ?? null,
            'Longitude' => $this->attributes['PlaceLongitude'] ?? null,
            'Zoom'      => $this->attributes['placeZoom']      ?? null
        ];
    }
}
