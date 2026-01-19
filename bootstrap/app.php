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
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'congregacion' => \App\Http\Middleware\EnsureCongregacion::class,
            'role' => \App\Http\Middleware\CheckRole::class,
        ]);

        // Excluir rutas de disponibilidad del CSRF (ya protegidas por token único)
        $middleware->validateCsrfTokens(except: [
            'disponibilidad/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
