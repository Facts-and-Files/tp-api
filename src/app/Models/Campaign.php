<?php

namespace App\Models;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Team;
use App\Models\Story;
use Illuminate\Support\Facades\DB;

class Campaign extends Model
{
    const CREATED_AT = null;
    const UPDATED_AT = null;

    protected $table = 'Campaign';

    protected $primaryKey = 'CampaignId';

    protected $guarded = ['CampaignId'];

    protected $casts = [
        'Public' => 'boolean'
    ];

    protected $hidden = [
        'pivot'
    ];

    protected $appends = [
        'Teams',
        'StoryIds'
    ];

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'TeamCampaign', 'CampaignId', 'TeamId');
    }

    public function stories(): BelongsToMany
    {
        return $this->belongsToMany(Story::class, 'StoryCampaign', 'CampaignId', 'StoryId');;
    }

    public function getTeamsAttribute(): Collection
    {
        return $this->teams()->get()->map(function ($team) {
            return [
                'TeamId' => $team->TeamId,
                'Name' => $team->Name
            ];
        });
    }

    public function getStoryIdsAttribute(): Collection
    {
        return $this->stories()->get()->map(function ($story) {
            return $story->StoryId;
        });
    }
}
