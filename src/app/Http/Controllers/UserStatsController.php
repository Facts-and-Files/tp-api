<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ResponseController;
use App\Http\Resources\UserStatsResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class UserStatsController extends ResponseController
{
    public function show(int $id): JsonResponse
    {
        try {
            $data = DB::select('SELECT * FROM user_stats_view WHERE UserId = ?', [$id]);

            if (empty($data)) {
                return $this->sendError('Not found', 'No statistics exists for this UserId.');
            }

            // cast all as integer
            $collection = collect($data[0])->map(function ($value) {
                return is_numeric($value) ? (int) $value : $value;
            });

            $resource = new UserStatsResource($collection);

            return $this->sendResponse($resource, 'UserStats fetched.');
        } catch (\Exception $exception) {
            return $this->sendError('Invalid data', $exception->getMessage(), 400);
        }
    }

}
