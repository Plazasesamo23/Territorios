<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     * Verifica si el usuario tiene el rol requerido
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $role  El rol mínimo requerido (user, admin, superadmin)
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Superadmin tiene acceso a todo
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Verificar según el rol requerido
        switch ($role) {
            case 'superadmin':
                // Solo superadmin puede acceder
                abort(403, 'No tienes permisos para acceder a esta sección.');
                break;

            case 'admin':
                // Admin o superadmin pueden acceder
                if (!$user->isAdmin()) {
                    abort(403, 'No tienes permisos para acceder a esta sección.');
                }
                break;

            case 'user':
                // Cualquier usuario autenticado puede acceder
                break;

            default:
                abort(403, 'Rol no válido.');
        }

        return $next($request);
    }
}
