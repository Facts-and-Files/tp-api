<?php

namespace App\Models;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Campaign;
use App\Models\User;

class Team extends Model
{
    const CREATED_AT = null;
    const UPDATED_AT = null;

    protected $table = 'Team';

    protected $primaryKey = 'TeamId';

    protected $hidden = ['Code'];

    protected $appends = ['Users', 'Campaigns'];

    protected $guarded = ['TeamId'];

    public function campaign(): BelongsToMany
    {
        return $this->belongsToMany(Campaign::class, 'TeamCampaign', 'TeamId', 'CampaignId');
    }

    public function user(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'TeamUser', 'TeamId', 'UserId');
    }

    public function getUsersAttribute(): Collection
    {
        return $this->user()->get()->map(function ($user) {
            return [
                'UserId' => $user->UserId,
                'WP_UserId' => $user->WP_UserId
            ];
        });
    }

    public function getCampaignsAttribute(): Collection
    {
        return $this->campaign()->get()->map(function ($campaign) {
            return [
                'CampaignId' => $campaign->CampaignId,
                'Name' => $campaign->Name
            ];
        });
    }

}
