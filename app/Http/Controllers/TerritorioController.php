<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Territorio;
use App\Models\Publicador;

class TerritorioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Territorio::with(['registros.publicador']);

        // Aplicar búsqueda si se proporciona
        if ($request->has('search') && $request->search != '') {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('numero', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('nombre', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('descripcion', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('notas', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Aplicar filtro por estado si se proporciona
        if ($request->has('estado') && $request->estado != '') {
            $estadoFiltro = $request->estado;

            // Como necesitamos filtrar por estados calculados dinámicamente,
            // obtenemos todos los territorios y luego filtramos
            $allTerritorios = Territorio::with(['registros.publicador'])->get();
            $territoriosFiltrados = $allTerritorios->filter(function($territorio) use ($estadoFiltro) {
                return $territorio->calcularEstado() === $estadoFiltro;
            });

            // Convertir a collection paginable
            $territorioIds = $territoriosFiltrados->pluck('id')->toArray();
            $query->whereIn('id', $territorioIds);
        }
        
        $territorios = $query->orderBy('numero')->paginate(12)->appends(request()->query());
        
        // Calcular estadísticas de estados con regla de 90 días
        $allTerritoriosForStats = Territorio::with(['registros'])->get();
        $estadisticas = [
            'libre' => 0,
            'activo' => 0,
            'atrasado' => 0,
            'archivo' => 0,
            'disponibles' => 0  // Territorios realmente asignables
        ];
        
        foreach ($allTerritoriosForStats as $territorio) {
            $estado = $territorio->calcularEstado();
            if (isset($estadisticas[$estado])) {
                $estadisticas[$estado]++;
            }
            
            // Contar territorios realmente disponibles (libres + que cumplan 90 días)
            if ($territorio->estaDisponibleParaAsignar()) {
                $estadisticas['disponibles']++;
            }
        }
        
        // Variable para mostrar el total (sin conflictos)
        $allTerritorios = $allTerritoriosForStats;
        
        return view('territorios.index', compact('territorios', 'estadisticas', 'allTerritorios'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('territorios.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $congregacionId = session('congregacion_activa_id');

        $request->validate([
            'numero' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) use ($congregacionId) {
                    // Verificar unicidad solo dentro de la congregación activa
                    $exists = Territorio::withoutGlobalScope('congregacion')
                        ->where('congregacion_id', $congregacionId)
                        ->where('numero', $value)
                        ->exists();

                    if ($exists) {
                        $fail('El número de territorio ya existe en esta congregación.');
                    }
                },
            ],
            'nombre' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string',
            'coordenadas_lat' => 'nullable|numeric|between:-90,90',
            'coordenadas_lng' => 'nullable|numeric|between:-180,180',
            'estado' => 'required|in:libre,activo,archivo,atrasado',
            'imagen' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            'notas' => 'nullable|string'
        ]);

        $data = $request->except(['imagen']);
        $data['activo'] = $request->has('activo') ? 1 : 0;

        // Manejar subida de imagen
        if ($request->hasFile('imagen')) {
            $imagen = $request->file('imagen');
            $nombreArchivo = $request->numero . '.jpg';
            $imagen->move(public_path('imagenes'), $nombreArchivo);
            $data['imagen_url'] = 'imagenes/' . $nombreArchivo;
        }

        Territorio::create($data);

        return redirect()->route('territorios.index')
            ->with('success', 'Territorio creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Territorio $territorio)
    {
        // Verificar que el territorio existe y tiene un ID válido
        if (!$territorio || !$territorio->id) {
            return redirect()->route('dashboard')->with('error', 'Territorio no encontrado.');
        }
        
        $territorio->load(['registros.publicador']);
        $registros = $territorio->registros()->with('publicador')->latest()->get();
        
        return view('territorios.show', compact('territorio', 'registros'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Territorio $territorio)
    {
        return view('territorios.edit', compact('territorio'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Territorio $territorio)
    {
        $congregacionId = session('congregacion_activa_id');
        $territorioId = $territorio->id;

        $request->validate([
            'numero' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) use ($congregacionId, $territorioId) {
                    // Verificar unicidad solo dentro de la congregación activa, excluyendo el actual
                    $exists = Territorio::withoutGlobalScope('congregacion')
                        ->where('congregacion_id', $congregacionId)
                        ->where('numero', $value)
                        ->where('id', '!=', $territorioId)
                        ->exists();

                    if ($exists) {
                        $fail('El número de territorio ya existe en esta congregación.');
                    }
                },
            ],
            'nombre' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string',
            'coordenadas_lat' => 'nullable|numeric|between:-90,90',
            'coordenadas_lng' => 'nullable|numeric|between:-180,180',
            'estado' => 'required|in:libre,activo,archivo,atrasado',
            'imagen' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            'notas' => 'nullable|string'
        ]);

        $data = $request->except(['imagen']);
        $data['activo'] = $request->has('activo') ? 1 : 0;

        // Manejar subida de imagen
        if ($request->hasFile('imagen')) {
            // Eliminar imagen anterior si existe
            $imagenAnterior = public_path('imagenes/' . $territorio->numero . '.jpg');
            if (file_exists($imagenAnterior)) {
                unlink($imagenAnterior);
            }

            $imagen = $request->file('imagen');
            $nombreArchivo = $request->numero . '.jpg';
            $imagen->move(public_path('imagenes'), $nombreArchivo);
            $data['imagen_url'] = 'imagenes/' . $nombreArchivo;
        }

        $territorio->update($data);

        return redirect()->route('territorios.show', $territorio)
            ->with('success', 'Territorio actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Territorio $territorio)
    {
        $territorio->delete();

        return redirect()->route('territorios.index')
            ->with('success', 'Territorio eliminado exitosamente.');
    }

    /**
     * Enviar WhatsApp con el territorio
     */
    public function enviarWhatsapp(Territorio $territorio)
    {
        $publicador = $territorio->publicadorActual();
        
        if (!$publicador) {
            return back()->with('error', 'Este territorio no tiene un publicador asignado.');
        }
        
        $mensaje = "Hola {$publicador->nombre}, aquí tienes el territorio {$territorio->numero}: {$territorio->imagen_url}";
        $telefono = str_replace(['+', ' ', '-'], '', $publicador->telefono);
        $url = "https://wa.me/{$telefono}?text=" . urlencode($mensaje);
        
        return redirect($url);
    }
}
