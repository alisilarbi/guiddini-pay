<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Http\Resources\Api\ErrorResource;
use Symfony\Component\HttpFoundation\Response;

class ValidateAdminApiKeys
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $adminKey = $request->header('x-admin-key');
        $adminSecret = $request->header('x-admin-secret');

        if (!$adminKey || !$adminSecret) {
            return (new ErrorResource([
                'http_code' => 401,
                'code' => 'INVALID_API_KEYS',
                'message' => 'Invalid API keys',
                'detail' => [
                    'validation' => 'The x-admin-key and x-admin-secret headers are required.'
                ],
                'meta' => []
            ]))->response();
        }

        $savedAdminKey = config('app.admin_api.admin_key');
        $savedAdminSecret = config('app.admin_api.admin_secret');

        if ($adminKey !== $savedAdminKey || $adminSecret !== $savedAdminSecret) {
            return (new ErrorResource([
                'http_code' => 401,
                'code' => 'INVALID_API_KEYS',
                'message' => 'Invalid API keys',
                'detail' => [
                    'validation' => 'The provided admin API keys are invalid.'
                ],
                'meta' => []
            ]))->response();
        }

        return $next($request);
    }
}
