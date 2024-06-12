<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Person extends Model
{
    const CREATED_AT = null;
    const UPDATED_AT = null;

    protected $table = 'Person';

    protected $primaryKey = 'PersonId';
}
