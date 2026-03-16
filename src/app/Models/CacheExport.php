<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CacheExport extends Model
{
    const CREATED_AT = null;
    const UPDATED_AT = null;

    protected $table = 'CacheExport';

    protected $fillable = [
        'ItemId',
        'Format',
        'GeneratedAt',
        'SourceUpdatedAt',
        'FilePath',
    ];

    protected $casts = [
        'GeneratedAt' => 'datetime',
        'SourceUpdatedAt' => 'datetime',
    ];
}
