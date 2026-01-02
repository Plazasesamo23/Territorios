<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class CambiarUsuarioController extends Controller
{
    /**
     * Mostrar selector de usuarios
     */
    public function index()
    {
        $user = auth()->user();
        $congregacionId = session('congregacion_activa_id');
        $usuarioOriginalId = session('usuario_original_id');

        // Obtener el usuario con permisos reales (el original si existe)
        $usuarioConPermisos = $usuarioOriginalId ? User::find($usuarioOriginalId) : $user;

        // Superadmin puede ver todos los usuarios de todas las congregaciones
        if ($usuarioConPermisos && $usuarioConPermisos->isSuperAdmin()) {
            $usuarios = User::orderBy('congregacion_id')
                ->orderByRaw("CASE
                    WHEN role = 'superadmin' THEN 0
                    WHEN role = 'admin' THEN 1
                    WHEN role = 'territorios' THEN 2
                    ELSE 3
                END")
                ->orderBy('name')
                ->get();
            $necesitaPassword = false;
        }
        // Admin puede ver solo usuarios de su congregacion
        elseif ($usuarioConPermisos && $usuarioConPermisos->isAdmin()) {
            $usuarios = User::where('congregacion_id', $congregacionId)
                ->where('role', '!=', 'superadmin')
                ->orderByRaw("CASE
                    WHEN role = 'admin' THEN 0
                    WHEN role = 'territorios' THEN 1
                    ELSE 2
                END")
                ->orderBy('name')
                ->get();
            $necesitaPassword = false;
        }
        // Usuario de territorios o normal ve los usuarios pero necesita password para admin
        else {
            $usuarios = User::where('congregacion_id', $congregacionId)
                ->where('role', '!=', 'superadmin')
                ->orderByRaw("CASE
                    WHEN role = 'admin' THEN 0
                    WHEN role = 'territorios' THEN 1
                    ELSE 2
                END")
                ->orderBy('name')
                ->get();
            $necesitaPassword = true;
        }

        // Obtener usuario original si existe
        $usuarioOriginal = $usuarioOriginalId ? User::find($usuarioOriginalId) : null;

        return view('cambiar-usuario', compact('usuarios', 'necesitaPassword', 'usuarioOriginal'));
    }

    /**
     * Realizar el cambio de usuario
     */
    public function cambiar(Request $request)
    {
        $request->validate([
            'usuario_id' => 'required|exists:users,id',
            'password' => 'nullable|string',
        ]);

        $currentUser = auth()->user();
        $targetUser = User::findOrFail($request->usuario_id);
        $congregacionId = session('congregacion_activa_id');
        $usuarioOriginalId = session('usuario_original_id');

        // Verificar permisos
        $puedesCambiarSinPassword = false;

        // Si hay un usuario original guardado, usar sus permisos
        $usuarioConPermisos = $usuarioOriginalId ? User::find($usuarioOriginalId) : $currentUser;

        // Superadmin puede cambiar a cualquier usuario
        if ($usuarioConPermisos && $usuarioConPermisos->isSuperAdmin()) {
            $puedesCambiarSinPassword = true;
        }
        // Admin puede cambiar a usuarios de su congregacion (excepto superadmin)
        elseif ($usuarioConPermisos && $usuarioConPermisos->isAdmin()) {
            if ($targetUser->congregacion_id == $usuarioConPermisos->congregacion_id && !$targetUser->isSuperAdmin()) {
                $puedesCambiarSinPassword = true;
            }
        }
        // Usuario de territorios cambiando a otro usuario de territorios (mismo nivel)
        elseif ($usuarioConPermisos && $usuarioConPermisos->isTerritoriosUser()) {
            // Solo puede cambiar a otro usuario territorios sin password
            if ($targetUser->isTerritoriosUser() && $targetUser->congregacion_id == $usuarioConPermisos->congregacion_id) {
                $puedesCambiarSinPassword = true;
            }
        }

        // Si no puede cambiar sin password, verificar la contraseña del admin
        if (!$puedesCambiarSinPassword) {
            $admin = User::where('congregacion_id', $congregacionId)
                ->where('role', 'admin')
                ->first();

            if (!$admin || !Hash::check($request->password, $admin->password)) {
                return redirect()->route('cambiar-usuario.index')
                    ->with('error', 'Contraseña incorrecta')
                    ->with('usuario_id_intento', $request->usuario_id);
            }
        }

        // Guardar el usuario original si es la primera vez que se cambia
        if (!$usuarioOriginalId) {
            session(['usuario_original_id' => $currentUser->id]);
            session(['congregacion_original_id' => $congregacionId]);
        }

        // Realizar el cambio de usuario
        Auth::login($targetUser);

        // Actualizar la congregacion activa si es necesario
        if ($targetUser->congregacion_id) {
            session(['congregacion_activa_id' => $targetUser->congregacion_id]);
        }

        // Redirigir segun el tipo de usuario
        if ($targetUser->isTerritoriosUser()) {
            return redirect()->route('panel-territorios')
                ->with('success', 'Has cambiado al usuario: ' . $targetUser->name);
        }

        return redirect()->route('dashboard')
            ->with('success', 'Has cambiado al usuario: ' . $targetUser->name);
    }

    /**
     * Volver al usuario original
     */
    public function volver()
    {
        $usuarioOriginalId = session('usuario_original_id');
        $congregacionOriginalId = session('congregacion_original_id');

        if (!$usuarioOriginalId) {
            return redirect()->route('dashboard')
                ->with('error', 'No hay usuario original al que volver');
        }

        $usuarioOriginal = User::find($usuarioOriginalId);

        if (!$usuarioOriginal) {
            session()->forget(['usuario_original_id', 'congregacion_original_id']);
            return redirect()->route('dashboard')
                ->with('error', 'El usuario original ya no existe');
        }

        // Volver al usuario original
        Auth::login($usuarioOriginal);

        // Restaurar congregacion original
        if ($congregacionOriginalId) {
            session(['congregacion_activa_id' => $congregacionOriginalId]);
        }

        // Limpiar la sesion de usuario original
        session()->forget(['usuario_original_id', 'congregacion_original_id']);

        // Redirigir segun el tipo de usuario
        if ($usuarioOriginal->isTerritoriosUser()) {
            return redirect()->route('panel-territorios')
                ->with('success', 'Has vuelto a tu cuenta: ' . $usuarioOriginal->name);
        }

        return redirect()->route('dashboard')
            ->with('success', 'Has vuelto a tu cuenta: ' . $usuarioOriginal->name);
    }
}
