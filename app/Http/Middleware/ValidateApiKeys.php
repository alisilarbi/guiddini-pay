<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Application;
use Illuminate\Http\Request;
use App\Http\Resources\ApiResponseResource;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Resources\PaymentResponseResource;

class ValidateApiKeys
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $appKey = $request->header('x-app-key');
        $secretKey = $request->header('x-secret-key');

        if (!$appKey || !$secretKey) {
            return (new PaymentResponseResource(['success' => false, 'code' => 'INVALID_API_KEYS', 'message' => 'Invalid API keys']))->response()->setStatusCode(401);
        }

        $application = Application::where('app_key', $appKey)
            ->where('app_secret', $secretKey)
            ->first();

        if (!$application) {
            return (new PaymentResponseResource(['success' => false, 'code' => 'INVALID_API_KEYS', 'message' => 'Invalid API keys']))->response()->setStatusCode(401);
        }

        $origin = $request->header('Origin') ?? $request->header('Referer');

        if ($origin && rtrim($origin, '/') !== rtrim($application->website_url, '/')) {
            return (new PaymentResponseResource(['success' => false, 'code' => 'UNAUTHORIZED_ORIGIN', 'message' => 'Unauthorized origin']))->response()->setStatusCode(403);
        }

        return $next($request);
    }
}
