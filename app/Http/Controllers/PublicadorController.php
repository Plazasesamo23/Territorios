<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Publicador;
use App\Models\Registro;

class PublicadorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Vista minimalista - solo datos básicos ordenados alfabéticamente
        $publicadores = Publicador::orderBy('nombre')
            ->orderBy('apellidos')
            ->get();

        return view('publicadores.index', compact('publicadores'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('publicadores.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'apellidos' => 'nullable|string|max:255',
            'telefono' => 'required|string|max:20',
            'notas' => 'nullable|string|max:1000',
            'activo' => 'boolean'
        ]);

        Publicador::create([
            'nombre' => $request->nombre,
            'apellidos' => $request->apellidos,
            'telefono' => $request->telefono,
            'notas' => $request->notas,
            'activo' => $request->boolean('activo', true)
        ]);

        return redirect()->route('publicadores.index')
            ->with('success', 'Publicador creado exitosamente.');
    }

    /**
     * Display the specified resource - SOLO PARA EDITAR DATOS BÁSICOS
     */
    public function show(Publicador $publicador)
    {
        // Cargar territorio actual si existe
        $registroActivo = $publicador->registros()
            ->whereNull('fecha_entrada')
            ->with('territorio')
            ->first();
        $publicador->territorio_actual = $registroActivo ? $registroActivo->territorio : null;
        
        return view('publicadores.show', compact('publicador'));
    }

    /**
     * NUEVA FUNCIÓN: Gestión de registros y territorios del publicador
     */
    public function registros(Publicador $publicador)
    {
        // Cargar registros con relaciones
        $registros = $publicador->registros()
            ->with('territorio')
            ->orderBy('fecha_salida', 'desc')
            ->get();

        // Calcular estadísticas
        $estadisticas = [
            'total_territorios' => $registros->count(),
            'territorios_completados' => $registros->where('fecha_entrada', '!=', null)->count(),
            'territorio_actual' => $registros->where('fecha_entrada', null)->first(),
            'promedio_dias' => 0,
            'total_dias_trabajados' => 0
        ];

        // Calcular promedio de días
        $completados = $registros->where('fecha_entrada', '!=', null);
        if ($completados->count() > 0) {
            $totalDias = $completados->sum(function ($registro) {
                return \Carbon\Carbon::parse($registro->fecha_salida)
                    ->diffInDays(\Carbon\Carbon::parse($registro->fecha_entrada));
            });
            $estadisticas['promedio_dias'] = round($totalDias / $completados->count(), 1);
            $estadisticas['total_dias_trabajados'] = $totalDias;
        }

        return view('publicadores.registros', compact('publicador', 'registros', 'estadisticas'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Publicador $publicador)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'apellidos' => 'nullable|string|max:255',
            'telefono' => 'required|string|max:20',
            'notas' => 'nullable|string|max:1000',
            'activo' => 'boolean'
        ]);

        $publicador->update([
            'nombre' => $request->nombre,
            'apellidos' => $request->apellidos,
            'telefono' => $request->telefono,
            'notas' => $request->notas,
            'activo' => $request->boolean('activo')
        ]);

        return redirect()->route('publicadores.show', $publicador)
            ->with('success', 'Publicador actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Publicador $publicador)
    {
        // Verificar si tiene registros activos
        $registroActivo = $publicador->registros()->whereNull('fecha_entrada')->first();
        
        if ($registroActivo) {
            return redirect()->route('publicadores.index')
                ->with('error', 'No se puede eliminar un publicador con territorios asignados.');
        }

        $publicador->delete();

        return redirect()->route('publicadores.index')
            ->with('success', 'Publicador eliminado exitosamente.');
    }
}
