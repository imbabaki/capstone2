<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\IsAdmin::class,
        ]);

        // Add cache headers for faster loading
        $middleware->append(\App\Http\Middleware\CacheResponse::class);

        // Add emergency shutdown check for all web routes
        $middleware->append(\App\Http\Middleware\CheckEmergencyShutdown::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
