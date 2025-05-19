<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyType extends Model
{
    const CREATED_AT = null;
    const UPDATED_AT = null;

    protected $table = 'PropertyType';

    protected $primaryKey = 'PropertyTypeId';
}
