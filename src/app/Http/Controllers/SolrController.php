<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ResponseController;
use App\Http\Resources\UpdateSolrResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;

class SolrController extends ResponseController
{
    public function update(): JsonResponse
    {
        $command = 'delta-import';
        $commit = 'true';
        $storyDeltaUpdatePath = '/solr/merged_core/dataimport';
        $storyDeltaUpdateOrigin = config('services.solr.uri');

        $deltaUpdateUrl = $storyDeltaUpdateOrigin
            . $storyDeltaUpdatePath
            . '?command=' . $command
            . '&commit=' . $commit;

        try {
            $response = Http::withoutVerifying()->get($deltaUpdateUrl);

            if (!$response->successful()) {
                throw new \Exception('Could not delta update Solr index.');
            }

            $responseData = $response->json();

            if ($responseData['status'] !== 'busy' && $responseData['status'] !== 'idle') {
                throw new \Exception('Could not delta update Solr index.');
            }

            $updateSolrResource = new UpdateSolrResource($responseData['statusMessages']);

            return $this->sendResponse($updateSolrResource, 'Delta updated Solr index.');
        } catch (\Exception $exception) {
            return $this->sendError('Bad Gateway', $exception->getMessage(), 502);
        }
    }
}
