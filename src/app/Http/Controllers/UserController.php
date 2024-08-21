<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\ResponseController;
use App\Models\User;
use App\Http\Resources\UserResource;

class UserController extends ResponseController
{
    public function index(Request $request): JsonResponse
    {
        $queryColumns = [
            'WP_UserId' => 'WP_UserId',
            'RoleId'    => 'RoleId',
            'WP_Role'   => 'WP_Role'
        ];

        $initialSortColumn = 'UserId';

        $model = new User();

        $data = $this->getDataByRequest($request, $model, $queryColumns, $initialSortColumn);

        if (!$data) {
            return $this->sendError('Invalid data', $request . ' not valid', 400);
        }

        $collection = UserResource::collection($data);

        return $this->sendResponseWithMeta($collection, 'Users fetched.');
    }

    public function show(int $id): JsonResponse
    {
        try {
            $data = User::findOrFail($id);
            $resource = new UserResource($data);

            return $this->sendResponse($resource, 'User fetched.');
        } catch (\Exception $exception) {
            return $this->sendError('Not found', $exception->getMessage());
        }
    }

    public function showWPUserIdsByUserId(Request $request): JsonResponse
    {
        $queryColumns = [
            'UserId' => 'UserId'
        ];

        $initialSortColumn = 'UserId';

        $model = new User();

        $data = $this->getDataByRequest($request, $model, $queryColumns, $initialSortColumn);

        if (!$data) {
            return $this->sendError('Invalid data', $request . ' not valid', 400);
        }

        $filteredData = $data->map(function ($user) {
            return $user->only(['UserId', 'WP_UserId']);
        });

        $collection = UserResource::collection($filteredData);

        return $this->sendResponseWithMeta($collection, 'User fetched.');
    }
}
