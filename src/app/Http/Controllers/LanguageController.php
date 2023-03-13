<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\ResponseController;
use App\Models\Language;
use App\Http\Resources\LanguageResource;

class LanguageController extends ResponseController
{
    /**
     * Display a paginated listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $data = $this->getDataByRequest($request);

        if (!$data) {
            return $this->sendError('Invalid data', $request . ' not valid', 400);
        }

        $collection = LanguageResource::collection($data);

        return $this->sendResponse($collection, 'Languages fetched.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $data = Language::findOrFail($id);
            $resource = new LanguageResource($data);

            return $this->sendResponse($resource, 'Language fetched.');
        } catch (\Exception $exception) {
            return $this->sendError('Not found', $exception->getMessage());
        }
    }

    /**
     * Get data defined by request
     *
     * @param  Request $request
     * @return array Collection
     */
    protected function getDataByRequest(Request $request): Collection
    {
        $queries = $request->query();

        $queryColumns = [
            'Name' => 'Name',
            'NameEnglish' => 'NameEnglish',
            'Code' => 'Code',
            'Code3' => 'Code3'
        ];

        $language = new Language();

        $data = $language->whereRaw('1 = 1');

        foreach ($queries as $queryName => $queryValue) {
            if (array_key_exists($queryName, $queryColumns)) {
                $data->where($queryColumns[$queryName], $queryValue);
            }
        }

        $data = $this->filterDataByQueries($data, $queries);

        return $data;
    }

    /**
     * Filter data by requested queries
     *
     * @param  array Builder $data
     * @param  array         $queries
     * @return array Collection
     */
    protected function filterDataByQueries(Builder $data, array $queries): Collection
    {
        $limit = $queries['limit'] ?? 100;
        $page = $queries['page'] ?? 1;
        $orderBy = $queries['orderBy'] ?? 'LanguageId';
        $orderBy = $orderBy === 'id' ? 'LanguageId' : $orderBy;
        $orderDir = $queries['orderDir'] ?? 'asc';
        $offset = $limit * ($page - 1);

        $filtered = $data
            ->limit($limit)
            ->offset($offset)
            ->orderBy($orderBy, $orderDir)
            ->get();

        return $filtered;
    }
}
