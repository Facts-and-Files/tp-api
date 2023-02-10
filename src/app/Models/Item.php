<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
        'DateStart',
        'DateEnd',
        'DatasetId',
        'OrderIndex',
        'TranscriptionStatusId',
        'DescriptionStatusId',
        'LocationStatusId',
        'TaggingStatusId',
        'AutomaticEnrichmentStatusId',
        'Manifest',
        'DescriptionLanguage',
        'LockedTime',
        'LockedUser',
        'DateStartDisplay',
        'DateEndDisplay',
        'Exported',
        'OldItemId',
        'edm:WebResource',
        'EuropeanaAttachment',
    ];

    /**
     * append new/renamed fields
     *
     * @var array
     */
    protected $appends = [
        'CompletionStatus'
    ];

// to harmonize the API regarding the existent database schema
// we make use some custom accessors and mutators

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
