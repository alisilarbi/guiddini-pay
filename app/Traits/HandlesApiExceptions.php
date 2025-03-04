<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use App\Exceptions\PaymentException;
use App\Http\Resources\ApiResponseResource;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\PaymentResponseResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;

trait HandlesApiExceptions
{
    protected function handleApiException(\Throwable $exception): JsonResponse
    {
        $statusCode = 500;
        $errorCode = 'INTERNAL_ERROR';
        $errors = [];
        $message = 'Internal server error';

        if ($exception instanceof PaymentException) {
            $statusCode = $exception->getStatusCode();
            $errorCode = $exception->getErrorCode();
            $message = $exception->getMessage();
            $errors = $exception->getErrors();
        } elseif ($exception instanceof ModelNotFoundException) {
            $statusCode = 404;
            $errorCode = 'NOT_FOUND';
            $message = 'Resource not found';
        } elseif ($exception instanceof ValidationException) {
            $statusCode = 422;
            $errorCode = 'VALIDATION_ERROR';
            $message = 'Validation failed';
            $errors = $exception->errors();
        }

        return $this->jsonApiErrorResponse(
            $message,
            $errorCode,
            $statusCode,
            $errors
        );
    }

    private function jsonApiErrorResponse(
        string $message,
        string $code,
        int $statusCode,
        array $errors = []
    ): JsonResponse {
        $response = [
            'success' => false,
            'code' => $code,
            'message' => $message,
            'errors' => $errors,
            'http_code' => $statusCode
        ];

        return (new PaymentResponseResource($response))
            ->response()
            ->setStatusCode($statusCode);
    }
}
