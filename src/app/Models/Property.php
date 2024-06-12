<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    const CREATED_AT = null;
    const UPDATED_AT = null;

    protected $table = 'Property';

    protected $primaryKey = 'PropertyId';

    protected $hidden = ['pivot'];
}
