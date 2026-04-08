<?php

namespace App\Exceptions;

use App\Http\Controllers\ResponseController;
use Illuminate\Http\JsonResponse;
use RuntimeException;
use Throwable;

final class AltoNotPreparedException extends RuntimeException
{

    public function __construct(
        private readonly int $storyId,
        string $message = 'The ALTO export has not been prepared.',
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function render($_request): JsonResponse
    {
        return ResponseController::sendError(
            'Conflict',
            [
                'prepare_post_url' => url("/stories/{$this->storyId}/items/export/mets"),
                $this->getMessage(),
            ],
            409
        );
    }
}
