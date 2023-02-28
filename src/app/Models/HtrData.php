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
    protected $table = 'HtrData';

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
    protected $primaryKey = 'HtrDataId';

    /**
     * The attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = ['HtrDataId', 'ItemId', 'Language'];

    /**
     * append properties
     *
     * @var array
     */
    protected $appends = ['Language'];

    /**
     * define n:n relationship to languages with a pivot table
     */
    public function language()
    {
        return $this->belongsToMany(Language::class, 'HtrDataLanguage', 'HtrDataId', 'LanguageId');
    }

// we make usage of some custom accessors and mutators

    /**
     * create Language property/attribute getter
     */
    public function getLanguageAttribute()
    {
        return $this->language()->get();
    }
}
