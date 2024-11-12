<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class Ceil implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes)
    {
        $value = ceil($value);
        return is_numeric($value) ? (int) $value : $value;
    }

    public function set($model, string $key, $value, array $attributes)
    {
        return $value;
    }
}
