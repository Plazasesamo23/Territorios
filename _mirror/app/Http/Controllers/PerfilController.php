<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PerfilController extends Controller
{
    /**
     * Mostrar página de perfil
     */
    public function index()
    {
        return view('perfil.index');
    }

    /**
     * Cambiar contraseña
     */
    public function cambiarPassword(Request $request)
    {
        $request->validate([
            'password' => ['required', 'confirmed', Password::min(6)],
        ], [
            'password.required' => 'La nueva contraseña es obligatoria.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
        ]);

        $request->user()->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('perfil.index')
            ->with('success', '¡Contraseña actualizada correctamente!');
    }

    /**
     * Actualizar nombre
     */
    public function actualizarNombre(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ], [
            'name.required' => 'El nombre es obligatorio.',
        ]);

        $request->user()->update([
            'name' => $request->name,
        ]);

        return redirect()->route('perfil.index')
            ->with('success', '¡Nombre actualizado correctamente!');
    }
}
