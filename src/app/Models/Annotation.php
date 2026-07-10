<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Annotation extends Model
{
    protected $table = 'Annotation';

    protected $primaryKey = 'AnnotationId';

    public $timestamps = false;

    protected $guarded = [];
}
