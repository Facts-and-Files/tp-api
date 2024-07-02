<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Events\PersonInserted;
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

    public function store(Request $request): JsonResponse
    {
        try {
            $person = new Person();
            $person->fill($request->all());
            $person->save();

            $person->items()->attach($request['ItemId']);

            PersonInserted::dispatch($request['ItemId']);

            $resource = new PersonResource($person);

            return $this->sendResponse($resource, 'Person inserted.');
        } catch (\Exception $exception) {
            return $this->sendError('Invalid data', $exception->getMessage(), 400);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $person = Person::findOrfail($id);
            $person->fill($request->all());
            $person->save();

            if (is_array($request['ItemIds'])) {
                $person->items()->sync($request['ItemIds']);
            }

            $resource = new PersonResource($person);

            return $this->sendResponse($resource, 'Person updated.');
        } catch(\Exception $exception) {
            return $this->sendError('Invalid data', $exception->getMessage(), 400);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $person = Person::findOrfail($id);
            $resource = $person->toArray();
            $resource = new PersonResource($resource);
            $person->delete();

            return $this->sendResponse($resource, 'Person deleted.');
        } catch(\Exception $exception) {
            return $this->sendError('Invalid data', $exception->getMessage(), 400);
        }
    }
}
