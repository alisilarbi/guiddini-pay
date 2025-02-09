<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateApiKeys
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // $allowedDomain = 'www.guididni.dz';
        // $allowedDomain = '127.0.0.1';
        $allowedDomain = 'http://localhost';
        $requestDomain = parse_url($request->fullUrl(), PHP_URL_HOST);

        if ($requestDomain !== $allowedDomain) {
            return response()->json(['error' => 'Unauthorized domain'], 403);
        }

        $appKey = $request->header('app_key');
        $secretKey = $request->header('secret_key');

        if (!$appKey || !$secretKey || !$this->isValidKeys($appKey, $secretKey)) {
            return response()->json(['error' => 'Invalid API keys'], 401);
        }

        return $next($request);
    }

    private function isValidKeys($appKey, $secretKey): bool
    {
        return \App\Models\Application::where('app_key', $appKey)
            ->where('secret_key', $secretKey)
            ->exists();
    }


    // public function handle(Request $request, Closure $next): Response
    // {
    //     $allowedDomain = '127.0.0.1';
    //     $requestDomain = parse_url($request->fullUrl(), PHP_URL_HOST);

    //     if ($requestDomain !== $allowedDomain) {
    //         return response()->json(['error' => 'Unauthorized domain'], 403);
    //     }

    //     return $next($request);


    //     // $url = $request->fullUrl();
    //     // dd($url);
    //     // dd($request->all());
    //     // $appKey = $request->header('app_key');
    //     // $secretKey = $request->header('secret_key');


    //     // if ($appKey !== 'your_app_key' || $secretKey !== 'your_secret_key') {
    //     //     return response()->json(['error' => 'Unauthorized'], 401);
    //     // }
    //     // return $next($request);


    //     // $allowedDomain = '127.0.0.1:8000/api/initiate';
    //     // $requestDomain = parse_url($request->headers->get('origin') ?? $request->headers->get('referer'), PHP_URL_HOST);


    //     // if ($requestDomain !== $allowedDomain) {
    //     //     return response()->json(['error' => 'Unauthorized domain'], 403);
    //     // }

    //     // $appKey = $request->header('app_key');
    //     // $secretKey = $request->header('secret_key');


    //     // if ($appKey !== 'your_app_key' || $secretKey !== 'your_secret_key') {
    //     //     return response()->json(['error' => 'Unauthorized'], 401);
    //     // }
    //     // return $next($request);
    // }
}
