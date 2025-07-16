<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Property extends Model
{
    const CREATED_AT = null;
    const UPDATED_AT = null;

    protected $table = 'Property';

    protected $primaryKey = 'PropertyId';

    protected $guarded = ['PropertyId'];

    protected $hidden = [
        'X_Coord',
        'Y_Coord',
        'Width',
        'Height',
        'pivot',
    ];

    public static function createRules(): array
    {
        return [
            'Value' => 'required|string|max:750',
            'PropertyTypeId' => 'required|integer',
            'Description' => 'sometimes|string|max:1000',
        ];
    }

    public static function updateRules(): array
    {
        return self::createRules();
    }

    public function propertyType(): BelongsTo
    {
        return $this->belongsTo(PropertyType::class, 'PropertyTypeId');
    }
}
