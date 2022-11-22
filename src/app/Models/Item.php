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
    protected $fillable = ['TranscriptionSource'];

    /**
     * hide old field names
     *
     * @var array
     */
    protected $hidden = [
        'Title',
        'CompletionStatusId',
        'StoryId',
        'ProjectItemId',
        'Description',
        'DateStart',
        'DateEnd',
        'DatasetId',
        'ImageLink',
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
}
