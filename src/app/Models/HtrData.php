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
     * The attributes that are mass assignable.
     *
     * @var string
     */
    protected $guarded = ['item_id', 'process_id', 'user_id'];
}
