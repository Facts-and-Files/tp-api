<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dataset extends Model
{
    const CREATED_AT = null;
    const UPDATED_AT = null;

    protected $table = 'Dataset';

    protected $primaryKey = 'DatasetId';

    protected $guarded = ['DatasetId'];
}
