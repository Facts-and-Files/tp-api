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
     * The attributes that are mass assignable.
     *
     * @var string
     */
    protected $fillable = ['transcription_source'];

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'ItemId';
}
