<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->group(base_path('routes/territorios.php'));

            Route::middleware('web')
                ->group(base_path('routes/ppoc.php'));

            Route::middleware('web')
                ->group(base_path('routes/admin.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'congregacion' => \App\Http\Middleware\EnsureCongregacion::class,
            'role' => \App\Http\Middleware\CheckRole::class,
        ]);

        // Excluir rutas de CSRF
        $middleware->validateCsrfTokens(except: [
            'disponibilidad/*',
            's13/importar/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
