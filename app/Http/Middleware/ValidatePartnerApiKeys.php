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

        $partnerKey = $request->header('x-partner-key');
        $partnerSecret = $request->header('x-partner-secret');

        if (!$partnerKey || !$partnerSecret) {
            return (new ErrorResource([
                'http_code' => 401,
                'code' => 'INVALID_API_KEYS',
                'message' => 'Invalid API keys',
                'detail' => null,
                'meta' => []
            ]))->response();
        }

        $partner = User::where('partner_key', $partnerKey)
            ->where('partner_secret', $partnerSecret)
            ->where('is_partner', true)
            ->first();

        if (!$partner) {
            return (new ErrorResource([
                'http_code' => 401,
                'code' => 'INVALID_API_KEYS',
                'message' => 'Invalid API keys',
                'detail' => null,
                'meta' => []
            ]))->response();
        }

        if (app()->environment('production')) {
            $origin = $request->header('Origin') ?? $request->header('Referer');

            if ($origin && rtrim($origin, '/') !== rtrim($partner->website_url, '/')) {
                return (new ErrorResource([
                    'http_code' => 403,
                    'code' => 'UNAUTHORIZED_ORIGIN',
                    'message' => 'Unauthorized origin',
                    'detail' => null,
                    'meta' => []
                ]))->response();
            }
        }

        $request->attributes->add(['partner' => $partner]);
        return $next($request);
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    // public function handle(Request $request, Closure $next): Response
    // {
    //     $partnerKey = $request->header('x-partner-key');
    //     $partnerSecret = $request->header('x-partner-secret');

    //     if (!$partnerKey || !$partnerSecret) {
    //         return (new ErrorResource([
    //             'http_code' => 401,
    //             'code' => 'INVALID_API_KEYS',
    //             'message' => 'Invalid API keys',
    //             'detail' => null,
    //             'meta' => []
    //         ]))->response();
    //     }

    //     $user = User::where('partner_key', $partnerKey)
    //         ->where('partner_secret', $partnerSecret)
    //         ->first();

    //     if (!$user) {
    //         return (new ErrorResource([
    //             'http_code' => 401,
    //             'code' => 'INVALID_API_KEYS',
    //             'message' => 'Invalid API keys',
    //             'detail' => null,
    //             'meta' => []
    //         ]))->response();
    //     }



    //     return $next($request);
    // }
}
