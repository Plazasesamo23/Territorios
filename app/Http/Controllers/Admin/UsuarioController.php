<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\User;
use App\Models\Congregacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class UsuarioController extends Controller
{
    /**
     * Mostrar lista de usuarios de la congregación
     */
    public function index()
    {
        $user = auth()->user();
        $congregacionId = session('congregacion_activa_id');

        // Obtener usuarios de la congregación activa
        $usuarios = User::where('congregacion_id', $congregacionId)
            ->orderBy('name')
            ->get();

        $congregacion = Congregacion::find($congregacionId);

        return view('usuarios.index', compact('usuarios', 'congregacion'));
    }

    /**
     * Mostrar formulario para crear usuario
     */
    public function create()
    {
        return view('usuarios.create');
    }

    /**
     * Guardar nuevo usuario
     */
    public function store(Request $request)
    {
        $congregacionId = session('congregacion_activa_id');

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Password::min(6)],
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
        ]);

        // Verificar que el nombre no exista ya en esta congregación
        $nombreExiste = User::where('congregacion_id', $congregacionId)
            ->where('name', $request->name)
            ->exists();

        if ($nombreExiste) {
            return back()->withErrors(['name' => 'Ya existe un usuario con este nombre en la congregación.'])->withInput();
        }

        // Generar email único automático basado en el nombre
        $emailBase = Str::slug($request->name, '.') . '.' . $congregacionId . '@territorios.local';
        
        // Asegurar unicidad del email
        $email = $emailBase;
        $counter = 1;
        while (User::where('email', $email)->exists()) {
            $email = Str::slug($request->name, '.') . '.' . $congregacionId . '.' . $counter . '@territorios.local';
            $counter++;
        }

        // Rol elegido en el formulario (solo roles no administrativos)
        $role = in_array($request->role, ['user', 'territorios', 'ppoc']) ? $request->role : 'user';

        User::create([
            'name' => $request->name,
            'email' => $email,
            'password' => Hash::make($request->password),
            'congregacion_id' => $congregacionId,
            'role' => $role,
            'puede_acceder_ppoc' => $role === 'ppoc',
        ]);

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario "' . $request->name . '" creado correctamente.');
    }

    /**
     * Mostrar formulario para editar usuario
     */
    public function edit(User $usuario)
    {
        $congregacionId = session('congregacion_activa_id');

        // Verificar que el usuario pertenece a la congregación
        if ($usuario->congregacion_id !== $congregacionId) {
            abort(403, 'No tienes permisos para editar este usuario.');
        }

        // No permitir editar superadmins
        if ($usuario->isSuperAdmin()) {
            abort(403, 'No puedes editar un superadministrador.');
        }

        return view('usuarios.edit', compact('usuario'));
    }

    /**
     * Actualizar usuario
     */
    public function update(Request $request, User $usuario)
    {
        $congregacionId = session('congregacion_activa_id');

        // Verificar permisos
        if ($usuario->congregacion_id !== $congregacionId) {
            abort(403, 'No tienes permisos para editar este usuario.');
        }

        if ($usuario->isSuperAdmin()) {
            abort(403, 'No puedes editar un superadministrador.');
        }

        $rules = [
            'name' => ['required', 'string', 'max:255'],
        ];

        // Solo validar contraseña si se proporciona
        if ($request->filled('password')) {
            $rules['password'] = ['confirmed', Password::min(6)];
        }

        $request->validate($rules, [
            'name.required' => 'El nombre es obligatorio.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
        ]);

        // Verificar que el nuevo nombre no exista ya en esta congregación (excepto el usuario actual)
        $nombreExiste = User::where('congregacion_id', $congregacionId)
            ->where('name', $request->name)
            ->where('id', '!=', $usuario->id)
            ->exists();

        if ($nombreExiste) {
            return back()->withErrors(['name' => 'Ya existe un usuario con este nombre en la congregación.'])->withInput();
        }

        $usuario->name = $request->name;

        if ($request->filled('password')) {
            $usuario->password = Hash::make($request->password);
        }

        // Actualizar rol y permisos (para todos los roles excepto admin/superadmin)
        if (!in_array($usuario->role, ['admin', 'superadmin'])) {
            if (in_array($request->role, ['user', 'territorios', 'ppoc'])) {
                $usuario->role = $request->role;
            }
            $usuario->puede_generar_s13 = $request->has('puede_generar_s13');
            $usuario->puede_acceder_ppoc = $request->has('puede_acceder_ppoc') || $usuario->role === 'ppoc';
        }

        $usuario->save();

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario actualizado correctamente.');
    }

    /**
     * Eliminar usuario
     */
    public function destroy(User $usuario)
    {
        $congregacionId = session('congregacion_activa_id');
        $currentUser = auth()->user();

        // Verificar permisos
        if ($usuario->congregacion_id !== $congregacionId) {
            abort(403, 'No tienes permisos para eliminar este usuario.');
        }

        if ($usuario->isSuperAdmin()) {
            abort(403, 'No puedes eliminar un superadministrador.');
        }

        // No permitir eliminarse a sí mismo
        if ($usuario->id === $currentUser->id) {
            return redirect()->route('usuarios.index')
                ->with('error', 'No puedes eliminarte a ti mismo.');
        }

        // No permitir eliminar admins (solo el propio admin puede ser eliminado por superadmin)
        if ($usuario->isAdmin() && !$currentUser->isSuperAdmin()) {
            return redirect()->route('usuarios.index')
                ->with('error', 'No puedes eliminar a un administrador.');
        }

        $usuario->delete();

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario eliminado correctamente.');
    }
}
