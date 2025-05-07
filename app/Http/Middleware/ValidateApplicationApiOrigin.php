<?php

namespace App\Http\Middleware;

use Log;
use Closure;
use Illuminate\Http\Request;
use App\Http\Resources\Api\ErrorResource;
use Symfony\Component\HttpFoundation\Response;

class ValidateApplicationApiOrigin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $application = $request->application;

        if ($application->license_env === 'production') {
            $origin = $request->header('Origin') ?? $request->header('Referer');

            dd([
                'origin' => $request->header('Origin'),
                'referer' => $request->header('Referer'),
            ]);

            // Check if origin is present and does not match the application's website URL
            if ($origin && rtrim($origin, '/') !== rtrim($application->website_url, '/')) {
                return (new ErrorResource([
                    'http_code' => 403,
                    'code' => 'UNAUTHORIZED_ORIGIN',
                    'message' => 'Unauthorized origin',
                    'detail' => null,
                    'meta' => []
                ]))->response()->setStatusCode(403);
            }
        }

        return $next($request);
    }
}
