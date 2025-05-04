<?php

namespace App\Traits;

use App\Models\User;
use App\Models\Application;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use App\Exceptions\PaymentException;
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

            switch ($exception->getModel()) {
                case User::class:
                    $message = 'The user ID does not exist';
                    break;
                case Application::class:
                    $message = 'The application ID does not exist';
                    break;
                case Transaction::class:
                    $message = 'The transaction ID does not exist';
                    break;
                default:
                    $message = 'Resource not found';
                    break;
            }
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
        } else if ($exception->getMessage() === 'QUOTA_DEPLETED') {
            $statusCode = 403;
            $errorCode = 'CREATION_LIMIT_REACHED';
            $message = 'Cannot create application';
            $detail = 'The partner has reached the maximum number of applications allowed.';
        } elseif ($exception->getMessage() === 'NO_QUOTA_TRANSACTION') {
            $statusCode = 400;
            $errorCode = 'NO_QUOTA_TRANSACTION';
            $message = 'No quota transaction available.';
            $detail = 'The partner has no quota transaction available.';
        };

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
