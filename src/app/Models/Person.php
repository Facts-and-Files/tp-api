<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

class Person extends Model
{
    const CREATED_AT = null;
    const UPDATED_AT = null;

    protected $table = 'Person';

    protected $primaryKey = 'PersonId';

    protected $guarded = ['PersonId'];

    protected $hidden = ['pivot', 'ItemId', 'StoryId'];

    protected $appends = ['ItemIds'];

    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'ItemPerson', 'PersonId', 'ItemId');
    }

    public function getItemIdsAttribute(): Collection
    {
        return $this->items()->pluck('Item.ItemId');
    }
}
