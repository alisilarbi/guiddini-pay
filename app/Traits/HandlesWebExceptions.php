<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;

trait HandlesWebExceptions
{
    /**
     * Handle exceptions for web routes and return an appropriate response.
     *
     * @param \Throwable $exception The thrown exception
     * @param Request $request The current request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    protected function handleWebException(\Throwable $exception, Request $request)
    {
        // Delegate to specific handlers based on exception type
        if ($exception instanceof ModelNotFoundException) {
            return $this->handleModelNotFound($exception, $request);
        } elseif ($exception instanceof HttpException) {
            return $this->handleHttpException($exception, $request);
        } else {
            return $this->handleGeneralException($exception, $request);
        }
    }

    /**
     * Handle ModelNotFoundException with a user-friendly redirect.
     */
    protected function handleModelNotFound(ModelNotFoundException $exception, Request $request)
    {
        $message = 'The requested resource could not be found.';
        Log::warning($message, ['exception' => $exception, 'request' => $request->all()]);

        return redirect()->back()
            ->with('error', $message)
            ->withInput();
    }

    /**
     * Handle HTTP exceptions (e.g., 403, 404) with appropriate messaging.
     */
    protected function handleHttpException(HttpException $exception, Request $request)
    {
        $statusCode = $exception->getStatusCode();
        $message = $exception->getMessage() ?: (Response::$statusTexts[$statusCode] ?? 'An error occurred');
        Log::warning("HTTP Exception: {$statusCode}", ['exception' => $exception]);

        return redirect()->back()
            ->with('error', $message)
            ->withInput();
    }

    /**
     * Fallback for unhandled exceptions.
     */
    protected function handleGeneralException(\Throwable $exception, Request $request)
    {
        Log::error('Unexpected error in web route', [
            'exception' => $exception,
            'request' => $request->all(),
        ]);

        return redirect()->back()
            ->with('error', 'Oops! Something went wrong. Please try again later.')
            ->withInput();
    }
}