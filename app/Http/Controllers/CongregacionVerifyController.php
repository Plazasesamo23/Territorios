<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Congregacion;
use Illuminate\Support\Facades\Hash;

class CongregacionVerifyController extends Controller
{
    /**
     * Muestra el formulario para verificar la contraseña de la congregación
     */
    public function showVerifyForm()
    {
        $congregacionId = session('congregacion_activa_id');

        if (!$congregacionId) {
            return redirect()->route('login');
        }

        $congregacion = Congregacion::find($congregacionId);

        if (!$congregacion) {
            return redirect()->route('login');
        }

        // Si la congregación no tiene contraseña configurada, verificar automáticamente
        if (empty($congregacion->password)) {
            $verificadas = session('congregaciones_verificadas', []);
            $verificadas[] = $congregacionId;
            session(['congregaciones_verificadas' => array_unique($verificadas)]);

            $urlDestino = session('url_destino', route('dashboard'));
            session()->forget('url_destino');

            return redirect($urlDestino);
        }

        return view('congregacion.verificar', compact('congregacion'));
    }

    /**
     * Verifica la contraseña de la congregación
     */
    public function verifyPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $congregacionId = session('congregacion_activa_id');

        if (!$congregacionId) {
            return redirect()->route('login');
        }

        $congregacion = Congregacion::find($congregacionId);

        if (!$congregacion) {
            return redirect()->route('login');
        }

        // Verificar la contraseña
        if (!Hash::check($request->password, $congregacion->password)) {
            return back()->withErrors(['password' => 'La contraseña de la congregación es incorrecta.']);
        }

        // Marcar esta congregación como verificada en la sesión
        $verificadas = session('congregaciones_verificadas', []);
        $verificadas[] = $congregacionId;
        session(['congregaciones_verificadas' => array_unique($verificadas)]);

        // Redirigir a la URL original o al dashboard
        $urlDestino = session('url_destino', route('dashboard'));
        session()->forget('url_destino');

        return redirect($urlDestino)->with('success', 'Acceso verificado correctamente.');
    }
}
