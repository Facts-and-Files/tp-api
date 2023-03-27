<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HtrDataRevision extends Model
{
    /**
     * The table associated with the model.
     *
     * Config over convention here to respect exsting table names.
     *
     * @var string
     */
    protected $table = 'HtrDataRevision';

    /**
     * Use user defined exisiting timestamp columns
     */
    const CREATED_AT = 'Timestamp';
    const UPDATED_AT = 'LastUpdated';

    /**
     * All of the relationships to be touched.
     *
     * @var array
     */
    protected $touches = ['HtrData'];

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'HtrDataRevisionId';

    /**
     * The attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = ['HtrDataRevisionId', 'HtrDataId', 'TranscriptionText'];

    /**
     * append properties
     *
     * @var array
     */
    protected $appends = [];

    /**
     * @return BelongsTo
     */
    public function htrData(): BelongsTo
    {
        return $this->belongsTo(HtrData::class, 'HtrDataId', 'HtrDataId');
    }
}
