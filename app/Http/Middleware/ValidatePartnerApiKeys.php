<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Http\Resources\Api\ErrorResource;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class ValidatePartnerApiKeys
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $appKey = $request->header('x-app-key');
        $secretKey = $request->header('x-secret-key');

        if (!$appKey || !$secretKey) {
            return (new ErrorResource([
                'http_code' => 401,
                'code' => 'INVALID_API_KEYS',
                'message' => 'Invalid API keys',
                'detail' => null,
                'meta' => []
            ]))->response();
        }

        $user = User::where('app_key', $appKey)
            ->where('app_secret', $secretKey)
            ->first();

        if (!$user) {
            return (new ErrorResource([
                'http_code' => 401,
                'code' => 'INVALID_API_KEYS',
                'message' => 'Invalid API keys',
                'detail' => null,
                'meta' => []
            ]))->response();
        }

        // $origin = $request->header('Origin') ?? $request->header('Referer');

        // if ($origin && rtrim($origin, '/') !== rtrim($user->website_url, '/')) {
        //     return (new ErrorResource([
        //         'http_code' => 403,
        //         'code' => 'UNAUTHORIZED_ORIGIN',
        //         'message' => 'Unauthorized origin',
        //         'detail' => null,
        //         'meta' => []
        //     ]))->response();
        // }

        return $next($request);
    }
}
