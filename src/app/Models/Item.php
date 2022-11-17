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
    /* protected $fillable = ['order_index', 'transcription_source']; */
    protected $fillable = ['transcription_source'];

    /**
     * append new field names
     *
     * @var array
     */
    protected $appends = [
        'id',
        'created_at',
        'updated_at'
    ];

    /**
     * hide old field names
     *
     * @var array
     */
    protected $hidden = [
        'ItemId',
        'Title',
        'CompletionStatusId',
        'StoryId',
        'ProjectItemId',
        'Description',
        'DateStart',
        'DateEnd',
        'DatasetId',
        'Timestamp',
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
        'LastUpdated',
        'EuropeanaAttachment',
    ];

    /**
     * new id getter alias
     *
     * @return string
     */
    public function getIdAttribute()
    {
        return $this->attributes['ItemId'];
    }

    /**
     * new created_at getter alias
     *
     * @return string
     */
    public function getCreatedAtAttribute()
    {
        return $this->attributes['Timestamp'];
    }

    /**
     * new updated_at getter alias
     *
     * @return string
     */
    public function getUpdatedAtAttribute()
    {
        return $this->attributes['LastUpdated'];
    }

    /**
     * new order_index getter alias
     *
     * @return string
     */
    /* public function getOrderIndexAttribute() */
    /* { */
    /*     return $this->attributes['OrderIndex']; */
    /* } */

    /**
     * new order_index setter alias
     *
     * @return string
     */
    /* public function setOrderIndexAttribute($value) */
    /* { */
    /*     $this->attributes['OrderIndex'] = $value; */
    /*     return $this->attributes['OrderIndex']; */
    /* } */
}
