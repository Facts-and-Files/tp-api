<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ResponseController;
use App\Http\Resources\PlaceResource;
use App\Models\Story;
use Illuminate\Http\JsonResponse;

class PlaceController extends ResponseController
{
    public function showByStoryId(int $storyId): JsonResponse
    {
        try {
            $story = Story::with('items.places')->find($storyId);
            $data = $story->items->pluck('places')->unique();
            $resource = new PlaceResource($data);

            return $this->sendResponse($resource, 'Places fetched.');
        } catch (\Exception $exception) {
            return $this->sendError('Not found', $exception->getMessage());
        }
    }
}
