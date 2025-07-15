<?php

namespace App\Exceptions;

use App\Http\Controllers\ResponseController;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;


class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    protected function unauthenticated($request, AuthenticationException $exception): JsonResponse
    {
        return ResponseController::sendError($exception->getMessage(), '', 401);
    }

    public function render($request, Throwable $exception): JsonResponse
    {
        if ($exception instanceof AuthenticationException) {
            return ResponseController::sendError($exception->getMessage(), '', 401);
        }

        if ($exception instanceof ModelNotFoundException) {
            return ResponseController::sendError('Not found', $exception->getMessage(), 404);
        }

        if ($exception instanceof NotFoundHttpException) {
            return ResponseController::sendError('Not found', $exception->getMessage(), 404);
        }

        if ($exception instanceof ValidationException) {
            return ResponseController::sendError('Unprocessable Content', $exception->getMessage(), 422);
        }

        if ($exception) {
            return ResponseController::sendError('Internal Server Error', $exception->getMessage(), 500);
        }

        return parent::render($request, $exception);
    }
}
