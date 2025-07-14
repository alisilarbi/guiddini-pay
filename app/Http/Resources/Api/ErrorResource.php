<?php

namespace App\Exceptions;

use Throwable;
use App\Models\User;
use App\Models\Application;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Exceptions\ErrorResource;
use Illuminate\Support\Facades\Log;
use App\Exceptions\PaymentException;
use App\Exceptions\ReceiptException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

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
     * Render a detailed JSON response for API exceptions using ErrorResource.
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
        // Model not found with specific messages
        elseif ($exception instanceof ModelNotFoundException) {
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
                    $model = class_basename($exception->getModel());
                    $message = "The {$model} ID does not exist";
                    break;
            }
        }
        // Validation errors with field-specific messages
        elseif ($exception instanceof ValidationException) {
            $statusCode = 422;
            $errorCode = 'VALIDATION_ERROR';
            $message = 'Validation failed';
            $errors = $exception->errors();
        }
        // SSL-related request errors
        elseif ($exception instanceof RequestException && strpos($exception->getMessage(), 'SSL') !== false) {
            $statusCode = 502;
            $errorCode = 'SSL_ERROR';
            $message = 'SSL verification failed';
        }
        // HTTP exceptions (e.g., unauthorized, forbidden)
        elseif ($exception instanceof HttpException) {
            $statusCode = $exception->getStatusCode();
            $errorCode = strtoupper(str_replace(' ', '_', $exception->getMessage())) ?: 'HTTP_ERROR';
            $message = $exception->getMessage() ?: 'An HTTP error occurred';
        }
        // Specific error messages from HandlesApiExceptions
        elseif ($exception->getMessage() === 'PROSPECT_CONVERTED') {
            $statusCode = 404;
            $errorCode = 'NOT_FOUND';
            $message = 'Resource not found';
            $detail = 'The prospect must not be converted';
        } elseif ($exception->getMessage() === 'QUOTA_DEPLETED') {
            $statusCode = 403;
            $errorCode = 'CREATION_LIMIT_REACHED';
            $message = 'Cannot create application';
            $detail = 'The partner has reached the maximum number of applications allowed.';
        } elseif ($exception->getMessage() === 'NO_QUOTA_TRANSACTION') {
            $statusCode = 400;
            $errorCode = 'NO_QUOTA_TRANSACTION';
            $message = 'No quota transaction available.';
            $detail = 'The partner has no quota transaction available.';
        } elseif ($exception->getMessage() === 'NO_QUOTA_AVAILABLE') {
            $statusCode = 400;
            $errorCode = 'NO_QUOTA_AVAILABLE';
            $message = 'No quota available.';
            $detail = 'The partner has no quota available.';
        }

        // Log for debugging
        Log::error("API Exception: {$message}", [
            'exception' => get_class($exception),
            'message' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);

        // Use ErrorResource for consistent formatting
        return new ErrorResource([
            'http_code' => $statusCode,
            'code' => $errorCode,
            'message' => $message,
            'detail' => $detail,
            'errors' => $errors
        ]);
    }
}
