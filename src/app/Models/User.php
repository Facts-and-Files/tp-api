<?php

namespace App\Models;

use App\Models\Team;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Model
{
    const CREATED_AT = 'Timestamp';
    const UPDATED_AT = null;

    protected $table = 'User';

    protected $primaryKey = 'UserId';

    protected $guarded = ['UserId', 'WP_UserId', 'RoleId', 'WP_Role', 'Token', 'EventUser'];

    protected $hidden = [
        'Token',
        'pivot'
    ];

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'TeamUser', 'UserId', 'TeamId');
    }
}
