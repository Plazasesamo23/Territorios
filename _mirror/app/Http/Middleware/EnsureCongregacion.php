<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use App\Models\Congregacion;
use Symfony\Component\HttpFoundation\Response;

class EnsureCongregacion
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Superadmin: puede cambiar entre congregaciones
        if ($user->isSuperAdmin()) {
            $congregacionActivaId = session('congregacion_activa_id');

            // Si no tiene congregación seleccionada, seleccionar la primera
            if (!$congregacionActivaId) {
                $primeraCongregacion = Congregacion::where('activa', true)->first();
                if ($primeraCongregacion) {
                    session(['congregacion_activa_id' => $primeraCongregacion->id]);
                    $congregacionActivaId = $primeraCongregacion->id;
                }
            }

            $congregacionActiva = Congregacion::find($congregacionActivaId);
            $todasCongregaciones = Congregacion::where('activa', true)->get();

            View::share('congregacionActiva', $congregacionActiva);
            View::share('todasCongregaciones', $todasCongregaciones);
            View::share('esSuperAdmin', true);
        } else {
            // Usuario normal: usar su congregación fija
            if (!$user->congregacion_id) {
                Auth::logout();
                return redirect()->route('login')
                    ->withErrors(['email' => 'Tu usuario no tiene una congregación asignada. Contacta al administrador.']);
            }

            session(['congregacion_activa_id' => $user->congregacion_id]);

            $congregacionActiva = $user->congregacion;
            View::share('congregacionActiva', $congregacionActiva);
            View::share('todasCongregaciones', collect([$congregacionActiva]));
            View::share('esSuperAdmin', false);
        }

        return $next($request);
    }
}
