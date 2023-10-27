<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\ResponseController;
use App\Models\Campaign;
use App\Http\Resources\CampaignResource;

class CampaignController extends ResponseController
{
    public function index(Request $request): JsonResponse
    {
        $queryColumns = [
            'Name' => 'Name',
            'DatasetId' => 'DatasetId'
        ];

        $initialSortColumn = 'CampaignId';

        $model =  new Campaign();

        $data = $this->getDataByRequest($request, $model, $queryColumns, $initialSortColumn);

        if (!$data) {
            return $this->sendError('Invalid data', $request . ' not valid', 400);
        }

        $collection = CampaignResource::collection($data);

        return $this->sendResponseWithMeta($collection, 'Campaigns fetched.');
    }

    public function show(int $id): JsonResponse
    {
        try {
            $data = Campaign::findOrFail($id);
            $resource = new CampaignResource($data);

            return $this->sendResponse($resource, 'Campaign fetched.');
        } catch (\Exception $exception) {
            return $this->sendError('Not found', $exception->getMessage());
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $campaign = new Campaign();
            $campaign->fill($request->all());
            $campaign->save();

            if (is_array($request['TeamIds'])) {
                $campaign->teams()->sync($request['TeamIds']);
            }

            return $this->sendResponse(new CampaignResource($campaign), 'Campaign inserted.');
        } catch (\Exception $exception) {
            return $this->sendError('Invalid data', $exception->getMessage(), 400);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $campaign = Campaign::findOrfail($id);
            $campaign->fill($request->all());
            $campaign->save();

            if (is_array($request['TeamIds'])) {
                $campaign->teams()->sync($request['TeamIds']);
            }

            return $this->sendResponse(new CampaignResource($campaign), 'Campaign updated.');
        } catch(\Exception $exception) {
            return $this->sendError('Invalid data', $exception->getMessage(), 400);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $campaign = Campaign::findOrfail($id);
            $resource = $campaign->toArray();
            $resource = new CampaignResource($resource);
            $campaign->delete();

            return $this->sendResponse($resource, 'Campaign deleted.');
        } catch(\Exception $exception) {
            return $this->sendError('Invalid data', $exception->getMessage(), 400);
        }
    }
}
