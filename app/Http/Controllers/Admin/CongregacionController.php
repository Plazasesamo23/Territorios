<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\Congregacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class CongregacionController extends Controller
{
    /**
     * Mostrar lista de congregaciones (solo superadmin)
     */
    public function index()
    {
        $congregaciones = Congregacion::withCount(['territorios', 'publicadores', 'users'])->get();

        return view('congregaciones.index', compact('congregaciones'));
    }

    /**
     * Cambiar la congregación activa (solo superadmin)
     */
    public function cambiar(Congregacion $congregacion)
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'No tienes permiso para cambiar de congregación.');
        }

        session(['congregacion_activa_id' => $congregacion->id]);

        return redirect()->back()->with('success', "Cambiado a: {$congregacion->nombre}");
    }

    /**
     * Formulario para crear nueva congregación
     */
    public function create()
    {
        return view('congregaciones.create');
    }

    /**
     * Guardar nueva congregación
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'codigo' => 'required|string|max:50|unique:congregaciones,codigo',
            'usuario' => 'required|string|max:255|unique:congregaciones,usuario',
            'password_plain' => 'required|string|max:255',
            'ciudad' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string',
        ]);

        $validated['activa'] = true;
        $validated['password'] = Hash::make($validated['password_plain']);

        Congregacion::create($validated);

        return redirect()->route('congregaciones.index')
            ->with('success', 'Congregación creada exitosamente.');
    }

    /**
     * Formulario para editar congregación
     */
    public function edit(Congregacion $congregacion)
    {
        return view('congregaciones.edit', compact('congregacion'));
    }

    /**
     * Actualizar congregación
     */
    public function update(Request $request, Congregacion $congregacion)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'codigo' => 'required|string|max:50|unique:congregaciones,codigo,' . $congregacion->id,
            'usuario' => 'required|string|max:255|unique:congregaciones,usuario,' . $congregacion->id,
            'password_plain' => 'required|string|max:255',
            'ciudad' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string',
            'activa' => 'boolean',
        ]);

        $validated['activa'] = $request->has('activa');
        $validated['password'] = Hash::make($validated['password_plain']);

        $congregacion->update($validated);

        return redirect()->route('congregaciones.index')
            ->with('success', 'Congregación actualizada exitosamente.');
    }

    /**
     * Eliminar congregación
     */
    public function destroy(Congregacion $congregacion)
    {
        // Verificar que no tenga territorios o publicadores
        if ($congregacion->territorios()->count() > 0 || $congregacion->publicadores()->count() > 0) {
            return redirect()->route('congregaciones.index')
                ->with('error', 'No se puede eliminar una congregación con territorios o publicadores.');
        }

        $congregacion->delete();

        return redirect()->route('congregaciones.index')
            ->with('success', 'Congregación eliminada exitosamente.');
    }
}
