<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    /**
     * Use user defined exisiting timestamp columns
     */
    const CREATED_AT = null;
    const UPDATED_AT = null;

    /**
     * The table associated with the model.
     *
     * Config over convention here to respect exsting table names.
     *
     * @var string
     */
    protected $table = 'Language';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'LanguageId';
}
