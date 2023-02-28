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
     * @var string
     */
    protected $guarded = ['HtrDataId', 'ItemId'];

    protected $appends = ['Languages'];

    /**
     * define n:n relationship
     */
    public function getLanguagesAttribute()
    {
        return $this
            ->belongsToMany(Language::class, 'HtrDataLanguage', 'HtrDataId', 'LanguageId')
            ->get();
    }
}
