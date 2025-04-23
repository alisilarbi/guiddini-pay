<?php

namespace App\Traits;

use App\Models\User;
use App\Models\Application;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log;
use App\Exceptions\PaymentException;
use App\Exceptions\ReceiptException;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

trait HandlesWebExceptions
{
    /**
     * Handle exceptions for web routes and display Filament notifications.
     *
     * @param \Throwable $exception The thrown exception
     * @return void
     */
    protected function handleWebException(\Throwable $exception): void
    {
        $message = 'An unexpected error occurred';
        $notificationType = 'danger';
        $logLevel = 'error';
        $logContext = ['message' => $exception->getMessage(), 'type' => get_class($exception)];

        if ($exception instanceof ReceiptException || $exception instanceof PaymentException) {
            $message = $exception->getMessage();
            $notificationType = 'danger';
            $logContext = [
                'message' => $message,
                'error_code' => $exception->getErrorCode(),
                'errors' => $exception->getErrors(),
                'detail' => $exception->getDetail(),
            ];
        } elseif ($exception instanceof ModelNotFoundException) {
            $logLevel = 'warning';
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
            $logContext = ['message' => $message, 'model' => $exception->getModel()];
        } elseif ($exception instanceof ValidationException) {
            $logLevel = 'warning';
            $message = 'Validation failed: ' . implode(', ', array_flatten($exception->errors()));
            $logContext = ['message' => $message, 'errors' => $exception->errors()];
        }

        Log::$logLevel('Web Exception', $logContext);

        Notification::make()
            ->title($message)
            ->{$notificationType}()
            ->send();
    }
}
