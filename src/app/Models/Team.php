<?php

namespace App\Models;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\User;

class Team extends Model
{
    const CREATED_AT = null;
    const UPDATED_AT = null;

    protected $table = 'Team';

    protected $primaryKey = 'TeamId';

    protected $hidden = ['Code'];
    protected $appends = ['UserIds'];

    public function user(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'TeamUser', 'TeamId', 'UserId');
    }

    public function getUserIdsAttribute(): Collection
    {
        return $this->user()->get()->pluck('UserId');
    }

}
