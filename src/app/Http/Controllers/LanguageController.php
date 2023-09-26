<?php

namespace App\Http\Controllers;

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
        $queryColumns = [
            'Name' => 'Name',
            'NameEnglish' => 'NameEnglish',
            'Code' => 'Code',
            'Code3' => 'Code3'
        ];

        $initialSortColumn = 'LanguageId';

        $model = new Language();

        $data = $this->getDataByRequest($request, $model, $queryColumns, $initialSortColumn);

        if (!$data) {
            return $this->sendError('Invalid data', $request . ' not valid', 400);
        }

        $collection = LanguageResource::collection($data);

        return $this->sendResponseWithMeta($collection, 'Languages fetched.');
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
}
