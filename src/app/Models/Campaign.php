<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    const CREATED_AT = null;
    const UPDATED_AT = null;

    protected $table = 'Campaign';

    protected $primaryKey = 'CampaignId';

    protected $guarded = ['CampaignId'];

    protected $hidden = [
        'pivot'
    ];

}
