<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Congregacion;

class VerifyCongregacionPassword
{
    /**
     * Rutas que NO requieren verificación de contraseña
     */
    protected array $except = [
        'login',
        'logout',
        'congregacion/verificar',
        'congregacion/verificar-password',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        // Si no está autenticado, no aplicar este middleware
        if (!auth()->check()) {
            return $next($request);
        }

        // Si es una ruta excluida, no verificar
        if ($this->shouldPassThrough($request)) {
            return $next($request);
        }

        $congregacionId = session('congregacion_activa_id');

        // Si no hay congregación activa, redirigir a login
        if (!$congregacionId) {
            return redirect()->route('login');
        }

        // Verificar si ya se verificó la contraseña de esta congregación
        $verificadas = session('congregaciones_verificadas', []);

        if (!in_array($congregacionId, $verificadas)) {
            // Guardar la URL a donde quería ir
            session(['url_destino' => $request->url()]);
            return redirect()->route('congregacion.verificar');
        }

        return $next($request);
    }

    protected function shouldPassThrough(Request $request): bool
    {
        foreach ($this->except as $except) {
            if ($request->is($except) || $request->routeIs($except)) {
                return true;
            }
        }
        return false;
    }
}
