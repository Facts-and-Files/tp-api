<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HtrData extends Model
{
    /**
     * The table associated with the model.
     *
     * Config over convention here to respect exsting table names.
     *
     * @var string
     */
    protected $table = 'htr_data';

    /**
     * The primary key associated with the table.
     *
     * Config over convention here to respect exsting column names.
     *
     * @var string
     */
    protected $primaryKey = 'ItemId';

    /**
     * Indicates if the model's ID is auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The storage format of the model's date columns.
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

}
