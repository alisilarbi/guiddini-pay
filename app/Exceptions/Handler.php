<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use App\Exceptions\PaymentException;
use App\Exceptions\ReceiptException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Handler extends ExceptionHandler
{
    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $exception)
    {
        // Apply to API requests only
        if ($request->expectsJson() || $request->is('api/*')) {
            return $this->renderApiException($request, $exception);
        }

        return parent::render($request, $exception);
    }

    /**
     * Render a detailed JSON response for API exceptions.
     */
    protected function renderApiException(Request $request, Throwable $exception)
    {
        $statusCode = 500;
        $errorCode = 'INTERNAL_ERROR';
        $message = 'An unexpected error occurred';
        $errors = [];
        $detail = null;

        // Custom payment/receipt exceptions
        if ($exception instanceof PaymentException || $exception instanceof ReceiptException) {
            $statusCode = $exception->getStatusCode();
            $errorCode = $exception->getErrorCode();
            $message = $exception->getMessage();
            $errors = $exception->getErrors();
            $detail = $exception->getDetail();
        }
        // Model not found (dynamic for any model)
        elseif ($exception instanceof ModelNotFoundException) {
            $statusCode = 404;
            $errorCode = 'NOT_FOUND';
            $model = class_basename($exception->getModel());
            $message = "{$model} not found";
        }
        // Validation errors with field-specific messages
        elseif ($exception instanceof ValidationException) {
            $statusCode = 422;
            $errorCode = 'VALIDATION_ERROR';
            $message = 'Validation failed';
            $errors = $exception->errors();
        }
        // HTTP exceptions (e.g., unauthorized, forbidden)
        elseif ($exception instanceof HttpException) {
            $statusCode = $exception->getStatusCode();
            $errorCode = strtoupper(str_replace(' ', '_', $exception->getMessage())) ?: 'HTTP_ERROR';
            $message = $exception->getMessage() ?: 'An HTTP error occurred';
        }
        // Fallback for unhandled exceptions
        else {
            $statusCode = method_exists($exception, 'getStatusCode') ? $exception->getStatusCode() : 500;
            $errorCode = 'ERROR_' . $statusCode;
            $message = $exception->getMessage() ?: 'Something went wrong';
        }

        // Log for debugging
        Log::error("API Exception: {$message}", [
            'exception' => get_class($exception),
            'message' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);

        // Build the JSON response
        return $this->jsonApiErrorResponse($message, $errorCode, $statusCode, $errors, $detail);
    }

    /**
     * Create a standardized JSON API error response.
     */
    protected function jsonApiErrorResponse(
        string $message,
        string $code,
        int $statusCode,
        array $errors = [],
        ?string $detail = null
    ) {
        $response = [
            'errors' => [
                [
                    'status' => (string) $statusCode,
                    'code' => $code,
                    'title' => $message,
                    'detail' => $detail,
                    'meta' => !empty($errors) ? $errors : null,
                ]
            ]
        ];

        // Remove null meta for cleaner output
        if (empty($response['errors'][0]['meta'])) {
            unset($response['errors'][0]['meta']);
        }

        return response()->json($response, $statusCode);
    }
}
