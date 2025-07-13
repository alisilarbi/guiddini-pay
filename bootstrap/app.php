<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'validate_application_api_keys' => \App\Http\Middleware\ValidateApplicationApiKeys::class,
            'validate_application_api_origin' => \App\Http\Middleware\ValidateApplicationApiOrigin::class,
            'validate_partner_api_keys' => \App\Http\Middleware\ValidatePartnerApiKeys::class,
            'validate_partner_api_origin' => \App\Http\Middleware\ValidatePartnerApiOrigin::class,
        ]);
    })
    ->withProviders([
        App\Providers\HandlerServiceProvider::class,
    ])
    ->withExceptions(function (Exceptions $exceptions) {})->create();
