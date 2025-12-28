<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Territorio;
use App\Models\Publicador;
use App\Models\Congregacion;

class TerritorioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Territorio::with(['registros.publicador']);

        // Filtrar por tipo de territorio
        $tipoFiltro = $request->get('tipo', 'todos');
        if ($tipoFiltro && $tipoFiltro !== 'todos') {
            $query->where('tipo', $tipoFiltro);
        }

        // Aplicar busqueda si se proporciona
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

            $allTerritorios = Territorio::with(['registros.publicador'])
                ->when($tipoFiltro !== 'todos', fn($q) => $q->where('tipo', $tipoFiltro))
                ->get();

            $territoriosFiltrados = $allTerritorios->filter(function($territorio) use ($estadoFiltro) {
                return $territorio->calcularEstado() === $estadoFiltro;
            });

            $territorioIds = $territoriosFiltrados->pluck('id')->toArray();
            $query->whereIn('id', $territorioIds);
        }

        $territorios = $query->orderBy('tipo')->orderBy('numero')->paginate(12)->appends(request()->query());

        // Calcular estadisticas de estados
        $allTerritoriosForStats = Territorio::with(['registros'])
            ->when($tipoFiltro !== 'todos', fn($q) => $q->where('tipo', $tipoFiltro))
            ->get();

        $estadisticas = [
            'libre' => 0,
            'activo' => 0,
            'atrasado' => 0,
            'archivo' => 0,
            'disponibles' => 0
        ];

        foreach ($allTerritoriosForStats as $territorio) {
            $estado = $territorio->calcularEstado();
            if (isset($estadisticas[$estado])) {
                $estadisticas[$estado]++;
            }

            if ($territorio->estaDisponibleParaAsignar()) {
                $estadisticas['disponibles']++;
            }
        }

        // Contar por tipo para mostrar en filtros
        $conteoTipos = [
            'todos' => Territorio::count(),
            'normal' => Territorio::where('tipo', 'normal')->count(),
            'campana' => Territorio::where('tipo', 'campana')->count(),
            'negocios' => Territorio::where('tipo', 'negocios')->count(),
        ];

        $allTerritorios = $allTerritoriosForStats;

        return view('territorios.index', compact('territorios', 'estadisticas', 'allTerritorios', 'tipoFiltro', 'conteoTipos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $tipo = $request->get('tipo', 'normal');
        $congregacionId = session('congregacion_activa_id');

        // Obtener siguiente numero disponible para el tipo
        $siguienteNumero = Territorio::getSiguienteNumero($congregacionId, $tipo);

        return view('territorios.create', compact('tipo', 'siguienteNumero'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $congregacionId = session('congregacion_activa_id');
        $tipo = $request->get('tipo', 'normal');

        $request->validate([
            'tipo' => 'required|in:normal,campana,negocios',
            'numero' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) use ($congregacionId, $tipo) {
                    // Verificar unicidad dentro de congregacion y tipo
                    $exists = Territorio::withoutGlobalScope('congregacion')
                        ->where('congregacion_id', $congregacionId)
                        ->where('tipo', $tipo)
                        ->where('numero', $value)
                        ->exists();

                    if ($exists) {
                        $prefijo = Territorio::PREFIJOS[$tipo] ?? '';
                        $fail("El territorio {$prefijo}{$value} ya existe en esta congregacion.");
                    }
                },
            ],
            'nombre' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string',
            'coordenadas_lat' => 'nullable|numeric|between:-90,90',
            'coordenadas_lng' => 'nullable|numeric|between:-180,180',
            'estado' => 'required|in:libre,activo,archivo,atrasado',
            'imagen' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            'imagen_url' => 'nullable|string|max:1000',
            'notas' => 'nullable|string'
        ]);

        $data = $request->except(['imagen']);
        $data['activo'] = $request->has('activo') ? 1 : 0;
        $data['tipo'] = $tipo;

        // Manejar subida de imagen
        if ($request->hasFile('imagen')) {
            $imagen = $request->file('imagen');
            $tipoSuffix = $tipo !== 'normal' ? '_' . $tipo : '';
            $nombreArchivo = $congregacionId . "_" . $request->numero . $tipoSuffix . ".jpg";
            $imagen->move(public_path('imagenes'), $nombreArchivo);
            $data['imagen_url'] = 'imagenes/' . $nombreArchivo;
        } elseif ($request->filled('imagen_url')) {
            $data['imagen_url'] = $request->imagen_url;
        }

        Territorio::create($data);

        $prefijo = Territorio::PREFIJOS[$tipo] ?? '';
        return redirect()->route('territorios.index', ['tipo' => $tipo])
            ->with('success', "Territorio {$prefijo}{$request->numero} creado exitosamente.");
    }

    /**
     * Display the specified resource.
     */
    public function show(Territorio $territorio)
    {
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
        $tipo = $request->get('tipo', $territorio->tipo);

        $request->validate([
            'tipo' => 'required|in:normal,campana,negocios',
            'numero' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) use ($congregacionId, $territorioId, $tipo) {
                    // Verificar unicidad dentro de congregacion y tipo, excluyendo el actual
                    $exists = Territorio::withoutGlobalScope('congregacion')
                        ->where('congregacion_id', $congregacionId)
                        ->where('tipo', $tipo)
                        ->where('numero', $value)
                        ->where('id', '!=', $territorioId)
                        ->exists();

                    if ($exists) {
                        $prefijo = Territorio::PREFIJOS[$tipo] ?? '';
                        $fail("El territorio {$prefijo}{$value} ya existe en esta congregacion.");
                    }
                },
            ],
            'nombre' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string',
            'coordenadas_lat' => 'nullable|numeric|between:-90,90',
            'coordenadas_lng' => 'nullable|numeric|between:-180,180',
            'estado' => 'required|in:libre,activo,archivo,atrasado',
            'imagen' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            'imagen_url' => 'nullable|string|max:1000',
            'notas' => 'nullable|string'
        ]);

        $data = $request->except(['imagen']);
        $data['activo'] = $request->has('activo') ? 1 : 0;

        // Manejar subida de imagen
        if ($request->hasFile('imagen')) {
            // Eliminar imagen anterior si existe
            $tipoSuffixOld = $territorio->tipo !== 'normal' ? '_' . $territorio->tipo : '';
            $imagenAnterior = public_path("imagenes/" . $territorio->congregacion_id . "_" . $territorio->numero . $tipoSuffixOld . ".jpg");
            if (file_exists($imagenAnterior)) {
                unlink($imagenAnterior);
            }

            $imagen = $request->file('imagen');
            $tipoSuffix = $tipo !== 'normal' ? '_' . $tipo : '';
            $nombreArchivo = $congregacionId . "_" . $request->numero . $tipoSuffix . ".jpg";
            $imagen->move(public_path('imagenes'), $nombreArchivo);
            $data['imagen_url'] = 'imagenes/' . $nombreArchivo;
        } elseif ($request->filled('imagen_url')) {
            $data['imagen_url'] = $request->imagen_url;
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
        $tipo = $territorio->tipo;
        $territorio->delete();

        return redirect()->route('territorios.index', ['tipo' => $tipo])
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

        if (!$publicador->telefono) {
            return back()->with('error', 'El publicador no tiene un numero de telefono registrado.');
        }

        $congregacion = Congregacion::find($territorio->congregacion_id);

        if (!$congregacion) {
            return back()->with('error', 'No se encontro la congregacion.');
        }

        $mensaje = $congregacion->getMensajeWhatsappFormateado($publicador, $territorio);

        $telefono = str_replace(['+', ' ', '-'], '', $publicador->telefono);
        $url = "https://wa.me/{$telefono}?text=" . urlencode($mensaje);

        return redirect($url);
    }
}
