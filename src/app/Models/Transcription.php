<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transcription extends Model
{
    const CREATED_AT = 'Timestamp';
    const UPDATED_AT = null;

    protected $table = 'Transcription';

    protected $primaryKey = 'TranscriptionId';

    protected  $casts = ['CurrentVersion' => 'boolean'];
}
