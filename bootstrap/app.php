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

            Route::middleware('web')
                ->group(base_path('routes/reuniones.php'));

            Route::middleware('web')
                ->group(base_path('routes/tareas.php'));
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
        // Manejar error 419 (CSRF token expirado) de forma amigable
        $exceptions->renderable(function (\Illuminate\Session\TokenMismatchException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Tu sesion ha expirado. Por favor, recarga la pagina.'], 419);
            }

            return redirect()->back()
                ->withInput($request->except('password', '_token'))
                ->withErrors(['token' => 'Tu sesion ha expirado. Por favor, intenta de nuevo.']);
        });
    })->create();
