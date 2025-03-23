<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use App\Exceptions\PaymentException;
use App\Http\Resources\API\ErrorResource;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

trait HandlesApiExceptions
{
    protected function handleApiException(\Throwable $exception): JsonResponse
    {
        $statusCode = 500;
        $errorCode = 'INTERNAL_ERROR';
        $errors = [];
        $message = 'Internal server error';
        $detail = null;

        if ($exception instanceof PaymentException) {
            $statusCode = $exception->getStatusCode();
            $errorCode = $exception->getErrorCode();
            $message = $exception->getMessage();
            $errors = $exception->getErrors();
            $detail = $exception->getDetail();
        } elseif ($exception instanceof ModelNotFoundException) {
            $statusCode = 404;
            $errorCode = 'NOT_FOUND';
            $message = 'Resource not found';
        } elseif ($exception instanceof ValidationException) {
            $statusCode = 422;
            $errorCode = 'VALIDATION_ERROR';
            $message = 'Validation failed';
            $errors = $exception->errors();
        } elseif ($exception instanceof RequestException && strpos($exception->getMessage(), 'SSL') !== false) {
            $statusCode = 502;
            $errorCode = 'SSL_ERROR';
            $message = 'SSL verification failed';
        } elseif ($exception->getMessage() === 'PROSPECT_CONVERTED') {
            $statusCode = 404;
            $errorCode = 'NOT_FOUND';
            $message = 'Resource not found';
            $detail = 'The prospect must not be converted';
        }

        return $this->jsonApiErrorResponse(
            $message,
            $errorCode,
            $statusCode,
            $errors,
            $detail
        );
    }

    private function jsonApiErrorResponse(
        string $message,
        string $code,
        int $statusCode,
        array $errors = [],
        ?string $detail = null
    ): JsonResponse {
        return response()->json([
            'errors' => [
                [
                    'status' => (string)$statusCode,
                    'code' => $code,
                    'title' => $message,
                    'detail' => $detail,
                    'meta' => $errors
                ]
            ]
        ], $statusCode);
    }
}
