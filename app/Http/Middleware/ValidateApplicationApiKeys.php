<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Application;
use Illuminate\Http\Request;
use App\Http\Resources\Api\ErrorResource;
use Symfony\Component\HttpFoundation\Response;

class ValidateApplicationApiKeys
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $appKey = $request->header('x-app-key');
        $secretKey = $request->header('x-app-secret');

        if (!$appKey || !$secretKey) {
            return (new ErrorResource([
                'http_code' => 401,
                'code' => 'INVALID_API_KEYS',
                'message' => 'Invalid API keys',
                'detail' => null,
                'meta' => []
            ]))->response();
        }

        $application = Application::where('app_key', $appKey)
            ->where('app_secret', $secretKey)
            ->first();

        $request->attributes->add(['application' => $application]);
        if (!$application) {
            return (new ErrorResource([
                'http_code' => 401,
                'code' => 'INVALID_API_KEYS',
                'message' => 'Invalid API keys',
                'detail' => null,
                'meta' => []
            ]))->response();
        }

        return $next($request);
    }
}
