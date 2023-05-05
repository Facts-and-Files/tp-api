<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

}
