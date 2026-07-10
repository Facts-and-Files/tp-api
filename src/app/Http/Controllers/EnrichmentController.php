<?php

namespace App\Http\Controllers;

use App\Http\Resources\EnrichmentExportResource;
use App\Models\Annotation;
use App\Models\Item;
use App\Models\Transcription;
use App\Services\EnrichmentExportQueryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class EnrichmentController extends ResponseController
{
    public function __construct(
        private readonly EnrichmentExportQueryService $queryService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $data = $this->queryService->get($request);

        return $this->sendResponse(
            EnrichmentExportResource::collection($data),
            'Enrichments fetched.',
        );
    }

    public function updateTranscription(Request $request, int $id): JsonResponse
    {
        $data = $this->validateExportUpdate($request);

        $transcription = Transcription::findOrFail($id);
        $transcription->EuropeanaAnnotationId = $data['EuropeanaAnnotationId'];
        $transcription->save();

        Item::where('ItemId', $transcription->ItemId)->update(['Exported' => 1]);

        return $this->sendResponse([
            'TranscriptionId' => $transcription->TranscriptionId,
            'EuropeanaAnnotationId' => $transcription->EuropeanaAnnotationId,
        ], 'Transcription updated.');
    }

    public function updateAnnotation(Request $request, int $id): JsonResponse
    {
        $data = $this->validateExportUpdate($request);

        $annotation = Annotation::findOrFail($id);
        $annotation->EuropeanaAnnotationId = $data['EuropeanaAnnotationId'];
        $annotation->save();

        Item::where('ItemId', $annotation->ItemId)->update(['Exported' => 1]);

        return $this->sendResponse([
            'AnnotationId' => $annotation->AnnotationId,
            'EuropeanaAnnotationId' => $annotation->EuropeanaAnnotationId,
        ], 'Annotation updated.');
    }

    private function validateExportUpdate(Request $request): array
    {
        $allowed = ['EuropeanaAnnotationId'];
        $payload = $request->all();
        $unknown = array_diff(array_keys($payload), $allowed);

        if ($unknown !== []) {
            throw new InvalidArgumentException('Unknown fields: ' . implode(', ', $unknown));
        }

        $validated = validator($payload, [
            'EuropeanaAnnotationId' => ['required', 'integer'],
        ])->validate();

        return $validated;
    }
}
