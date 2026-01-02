<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Registro;
use App\Models\Territorio;
use App\Models\Publicador;
use Carbon\Carbon;

class RegistroController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Filtro por tipo de territorio
        $tipoFiltro = $request->get('tipo', 'todos');

        // Obtener IDs de territorios de la congregacion activa, filtrados por tipo si aplica
        $territorioQuery = Territorio::query();
        if ($tipoFiltro && $tipoFiltro !== 'todos') {
            $territorioQuery->where('tipo', $tipoFiltro);
        }
        $territorioIds = $territorioQuery->pluck('id');

        // Consulta base para registros activos (sin fecha de entrada) de la congregacion
        $query = Registro::with(['territorio', 'publicador'])
            ->whereIn('territorio_id', $territorioIds)
            ->whereNull('fecha_entrada');

        // Aplicar busqueda si se proporciona
        if ($request->has('search') && $request->search != '') {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->whereHas('territorio', function($territorialQ) use ($searchTerm) {
                    $territorialQ->where('numero', 'LIKE', "%{$searchTerm}%")
                                 ->orWhere('zona', 'LIKE', "%{$searchTerm}%");
                })
                ->orWhereHas('publicador', function($publicadorQ) use ($searchTerm) {
                    $publicadorQ->where('nombre', 'LIKE', "%{$searchTerm}%")
                                ->orWhere('apellidos', 'LIKE', "%{$searchTerm}%")
                                ->orWhere('telefono', 'LIKE', "%{$searchTerm}%");
                })
                ->orWhere('notas', 'LIKE', "%{$searchTerm}%");
            });
        }

        $registrosActivos = $query->get();

        // Calcular dias transcurridos y estado para cada registro activo
        foreach ($registrosActivos as $registro) {
            $registro->dias_transcurridos = Carbon::parse($registro->fecha_salida)->diffInDays(now());
            $registro->estado_calculado = $registro->territorio->calcularEstado();
        }

        // Conteo por tipo (para todos los registros activos sin filtrar)
        $todosTerritorioIds = Territorio::pluck('id');
        $todosRegistrosActivos = Registro::with('territorio')
            ->whereIn('territorio_id', $todosTerritorioIds)
            ->whereNull('fecha_entrada')
            ->get();

        $conteoTipos = [
            'todos' => $todosRegistrosActivos->count(),
            'normal' => $todosRegistrosActivos->filter(fn($r) => $r->territorio->tipo === 'normal')->count(),
            'campana' => $todosRegistrosActivos->filter(fn($r) => $r->territorio->tipo === 'campana')->count(),
            'negocios' => $todosRegistrosActivos->filter(fn($r) => $r->territorio->tipo === 'negocios')->count(),
        ];

        // Estadisticas rapidas (solo registros activos filtrados)
        $estadisticas = [
            'activos_total' => $registrosActivos->count(),
            'atrasados' => $registrosActivos->where('estado_calculado', 'atrasado')->count(),
            'territorios_disponibles' => Territorio::get()->filter(function($territorio) {
                return $territorio->estaDisponibleParaAsignar();
            })->count(),
            'promedio_dias' => $registrosActivos->avg('dias_transcurridos') ? round($registrosActivos->avg('dias_transcurridos'), 1) : 0
        ];

        // Historial reciente solo para referencia (ya no se usa en la vista)
        $historialReciente = collect();

        return view('registros.index', compact('registrosActivos', 'historialReciente', 'estadisticas', 'tipoFiltro', 'conteoTipos'));
    }

    /**
     * Mostrar registros archivados (completados)
     */
    public function archivados()
    {
        // Obtener IDs de territorios de la congregación activa
        $territorioIds = Territorio::pluck('id');

        // Registros archivados (con fecha de entrada) de la congregación
        $registrosArchivados = Registro::with(['territorio', 'publicador'])
            ->whereIn('territorio_id', $territorioIds)
            ->whereNotNull('fecha_entrada')
            ->orderBy('fecha_entrada', 'desc')
            ->get();

        // Calcular duración para cada registro archivado
        foreach ($registrosArchivados as $registro) {
            $registro->duracion_dias = Carbon::parse($registro->fecha_salida)
                ->diffInDays(Carbon::parse($registro->fecha_entrada));
        }

        // Estadísticas de archivados
        $estadisticas = [
            'total_archivados' => $registrosArchivados->count(),
            'promedio_dias' => $registrosArchivados->avg('duracion_dias') ? round($registrosArchivados->avg('duracion_dias'), 1) : 0,
            'min_dias' => $registrosArchivados->min('duracion_dias') ?? 0,
            'max_dias' => $registrosArchivados->max('duracion_dias') ?? 0
        ];

        return view('registros.archivados', compact('registrosArchivados', 'estadisticas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Solo territorios disponibles para asignar (que cumplan regla de 90 días)
        $territoriosDisponibles = Territorio::get()->filter(function($territorio) {
            return $territorio->estaDisponibleParaAsignar();
        });

        // Territorios no disponibles (para mostrar información)
        $territoriosNoDisponibles = Territorio::get()->filter(function($territorio) {
            return !$territorio->estaDisponibleParaAsignar() && $territorio->calcularEstado() === 'libre';
        });

        // Solo publicadores activos
        $publicadoresActivos = Publicador::where('activo', true)->get();

        return view('registros.create', compact('territoriosDisponibles', 'territoriosNoDisponibles', 'publicadoresActivos'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'territorio_id' => 'required|exists:territorios,id',
            'publicador_id' => 'required|exists:publicadores,id',
            'notas' => 'nullable|string|max:1000'
        ]);

        // Verificar que el territorio esté disponible (libre + cumple 90 días)
        $territorio = Territorio::findOrFail($request->territorio_id);
        if (!$territorio->estaDisponibleParaAsignar()) {
            $motivo = $territorio->motivoNoDisponible();
            return redirect()->back()
                ->withInput()
                ->with('error', "El territorio seleccionado no está disponible para asignar. Motivo: {$motivo}");
        }

        // Verificar que el publicador esté activo
        $publicador = Publicador::findOrFail($request->publicador_id);
        if (!$publicador->activo) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'El publicador seleccionado no está activo.');
        }

        // RESTRICCIÓN ELIMINADA: Los publicadores pueden tener múltiples territorios
        // Algunos hermanos pueden manejar 2-3 territorios simultáneamente

        // Crear el registro
        $registro = Registro::create([
            'territorio_id' => $request->territorio_id,
            'publicador_id' => $request->publicador_id,
            'fecha_salida' => now(),
            'fecha_entrada' => null,
            'notas' => $request->notas
        ]);

        // Preparar WhatsApp automático
        $this->enviarWhatsAppAsignacion($territorio, $publicador);

        return redirect()->route('registros.index')
            ->with('success', "Territorio {$territorio->numero_completo} asignado a {$publicador->nombre}.")
            ->with('mostrar_whatsapp', true);
    }

    /**
     * Display the specified resource.
     */
    public function show(Registro $registro)
    {
        $registro->load(['territorio', 'publicador']);
        
        // Calcular estadísticas del registro
        $estadisticas = [
            'dias_transcurridos' => null,
            'duracion_total' => null,
            'estado_actual' => null
        ];

        if ($registro->fecha_entrada) {
            // Registro cerrado
            $estadisticas['duracion_total'] = Carbon::parse($registro->fecha_salida)
                ->diffInDays(Carbon::parse($registro->fecha_entrada));
        } else {
            // Registro activo
            $estadisticas['dias_transcurridos'] = Carbon::parse($registro->fecha_salida)->diffInDays(now());
            $estadisticas['estado_actual'] = $registro->territorio->calcularEstado();
        }

        return view('registros.show', compact('registro', 'estadisticas'));
    }

    /**
     * Marcar entrada (devolver territorio)
     */
    public function marcarEntrada(Request $request, Registro $registro)
    {
        // Verificar que el registro esté activo
        if ($registro->fecha_entrada) {
            return redirect()->route('registros.index')
                ->with('error', 'Este registro ya está cerrado.');
        }

        // Validar fecha de entrada si se proporciona
        if ($request->has('fecha_entrada')) {
            $request->validate([
                'fecha_entrada' => 'required|date|after_or_equal:' . $registro->fecha_salida->format('Y-m-d')
            ]);
            $fechaEntrada = Carbon::parse($request->fecha_entrada);
        } else {
            $fechaEntrada = now();
        }

        // Marcar fecha de entrada
        $registro->update([
            'fecha_entrada' => $fechaEntrada
        ]);

        $duracionDias = Carbon::parse($registro->fecha_salida)->diffInDays($fechaEntrada);

        return redirect()->route('registros.index')
            ->with('success', "Territorio {$registro->territorio->numero_completo} devuelto por {$registro->publicador->nombre_completo}. Duracion: {$duracionDias} dias.");
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Registro $registro)
    {
        $registro->load(['territorio', 'publicador']);
        return view('registros.edit', compact('registro'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Registro $registro)
    {
        $request->validate([
            'notas' => 'nullable|string|max:1000',
            'fecha_salida' => 'required|date',
            'fecha_entrada' => 'nullable|date|after:fecha_salida'
        ]);

        $registro->update([
            'notas' => $request->notas,
            'fecha_salida' => $request->fecha_salida,
            'fecha_entrada' => $request->fecha_entrada
        ]);

        return redirect()->route('registros.index')
            ->with('success', 'Registro actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Registro $registro)
    {
        $territorio = $registro->territorio;
        $publicador = $registro->publicador;
        
        $registro->delete();

        return redirect()->route('registros.index')
            ->with('success', "Registro de territorio {$territorio->numero_completo} y {$publicador->nombre} eliminado.");
    }

    /**
     * Enviar mensaje de WhatsApp para asignación
     */
    private function enviarWhatsAppAsignacion($territorio, $publicador)
    {
        // Obtener el mensaje personalizado de la congregación
         $congregacion = $territorio->congregacion;
        $mensaje = $congregacion->getMensajeWhatsappFormateado($publicador, $territorio);

        // Limpiar número de teléfono y crear URL de WhatsApp
        $telefono = preg_replace('/[^0-9]/', '', $publicador->telefono);
        $whatsappUrl = "https://wa.me/{$telefono}?text=" . urlencode($mensaje);
        
        // Guardar la URL en sesión para redirigir después
        session(['whatsapp_url' => $whatsappUrl]);
        session(['whatsapp_publicador' => $publicador->nombre_completo]);
        session(['whatsapp_territorio' => $territorio->numero_completo]);
        session(['whatsapp_imagen_url' => $territorio->getImagenUrl()]);
        session(["whatsapp_mensaje" => $mensaje]);
        
        // Registrar el envío
        \Log::info("WhatsApp preparado para {$publicador->telefono}: {$mensaje}");
        
        return true;
    }

    /**
     * Obtener estadísticas para API/AJAX
     */
    public function estadisticas()
    {
        // Obtener IDs de territorios de la congregación activa
        $territorioIds = Territorio::pluck('id');

        $registrosActivos = Registro::whereIn('territorio_id', $territorioIds)
            ->whereNull('fecha_entrada')->count();
        $territoriosLibres = Territorio::get()->filter(function($territorio) {
            return $territorio->calcularEstado() === 'libre';
        })->count();

        return response()->json([
            'registros_activos' => $registrosActivos,
            'territorios_libres' => $territoriosLibres,
            'publicadores_activos' => Publicador::where('activo', true)->count()
        ]);
    }
}
