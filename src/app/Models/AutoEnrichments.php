<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AutoEnrichments extends Model
{
    /**
     * The table associated with the model.
     *
     * Config over convention here to respect exsting table names.
     *
     * @var string
     */
    protected $table = 'AutoEnrichments';

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
    protected $guarded = ['AutoEnrichmentId'];
}
