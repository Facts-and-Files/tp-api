<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\ResponseController;
use App\Models\Person;
use App\Http\Resources\PersonResource;

class PersonController extends ResponseController
{
    public function index(Request $request): JsonResponse
    {
        $queryColumns = [
            'FirstName'  => 'FirstName',
            'LastName'   => 'LastName',
            'BirthPlace' => 'BirthPlace',
            'DeathPlace' => 'DeathPlace',
            'PersonRole' => 'PersonRole'
        ];

        $initialSortColumn = 'PersonId';

        $model = new Person();

        $data = $this->getDataByRequest($request, $model, $queryColumns, $initialSortColumn);

        if (!$data) {
            return $this->sendError('Invalid data', $request . ' not valid', 400);
        }

        $collection = PersonResource::collection($data);

        return $this->sendResponseWithMeta($collection, 'Persons fetched.');
    }

    public function show(int $id): JsonResponse
    {
        try {
            $data = Person::findOrFail($id);
            $resource = new PersonResource($data);

            return $this->sendResponse($resource, 'Person fetched.');
        } catch (\Exception $exception) {
            return $this->sendError('Not found', $exception->getMessage());
        }
    }

    // public function store(Request $request): JsonResponse
    // {
    //     try {
    //         $team = new Team();
    //         $team->fill($request->all());
    //         $team->save();
    //
    //         if (is_array($request['UserIds'])) {
    //             $team->user()->sync($request['UserIds']);
    //         }
    //
    //         $resource = new TeamResource($team);
    //
    //         return $this->sendResponse($resource, 'Team inserted.');
    //     } catch (\Exception $exception) {
    //         return $this->sendError('Invalid data', $exception->getMessage(), 400);
    //     }
    // }
    //
    // public function update(Request $request, int $id): JsonResponse
    // {
    //     try {
    //         $team = Team::findOrfail($id);
    //         $team->fill($request->all());
    //         $team->save();
    //
    //         if (is_array($request['UserIds'])) {
    //             $team->user()->sync($request['UserIds']);
    //         }
    //
    //         $resource = new TeamResource($team);
    //
    //         return $this->sendResponse(new TeamResource($team), 'Team updated.');
    //     } catch(\Exception $exception) {
    //         return $this->sendError('Invalid data', $exception->getMessage(), 400);
    //     }
    // }
    //
    // public function destroy(int $id): JsonResponse
    // {
    //     try {
    //         $team = Team::findOrfail($id);
    //         $resource = $team->toArray();
    //         $resource = new TeamResource($resource);
    //         $team->delete();
    //
    //         return $this->sendResponse($resource, 'Team deleted.');
    //     } catch(\Exception $exception) {
    //         return $this->sendError('Invalid data', $exception->getMessage(), 400);
    //     }
    // }
}
