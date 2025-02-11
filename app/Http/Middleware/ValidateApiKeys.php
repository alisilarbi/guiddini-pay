<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Application;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateApiKeys
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $allowedOrigins = ['http://localhost', 'http://localhost:3000'];
        $origin = $request->header('Origin') ?? $request->header('Referer');

        if ($origin && !in_array(rtrim($origin, '/'), $allowedOrigins)) {
            return response()->json(['error' => 'Unauthorized origin'], 403);
        }

        $appKey = $request->header('x-app-key');
        $secretKey = $request->header('x-secret-key');

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
}
