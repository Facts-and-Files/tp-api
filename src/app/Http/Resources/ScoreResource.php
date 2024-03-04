<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ScoreResource extends JsonResource
{
    public function toArray($request): array
    {
        return parent::toArray($request);
    }
}
