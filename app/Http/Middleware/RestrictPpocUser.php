<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictPpocUser
{
    /**
     * Restringe a usuarios PPOC a solo acceder a rutas PPOC
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        
        if ($user && $user->isPpocUser()) {
            // Usuario PPOC solo puede acceder a rutas que empiecen con /ppoc
            $path = $request->path();
            
            // Permitir rutas PPOC y logout
            if (!str_starts_with($path, 'ppoc') && $path !== 'logout') {
                return redirect()->route('ppoc.calendario');
            }
        }
        
        return $next($request);
    }
}
