<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AutoEnrichment extends Model
{
    /**
     * The table associated with the model.
     *
     * Config over convention here to respect exsting table names.
     *
     * @var string
     */
    protected $table = 'AutoEnrichment';

    /**
     * Use user defined exisiting timestamp columns
     */
    const CREATED_AT = 'Timestamp';
    const UPDATED_AT = 'LastUpdated';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'AutoEnrichmentId';

    /**
     * The attributes that are not mass assignable.
     *
     * @var string
     */
    protected $guarded = ['AutoEnrichmentId', 'ItemId', 'StoryId'];
}
