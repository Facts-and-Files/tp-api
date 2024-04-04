<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TeamStatsResource extends JsonResource
{
    public function toArray($request): array
    {
        return parent::toArray($request);
    }
}
