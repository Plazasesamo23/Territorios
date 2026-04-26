<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Publicador;
use App\Models\Registro;
use App\Models\GrupoPredicacion;
use Carbon\Carbon;

class PublicadorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Publicador::query();

        if ($request->has('search') && $request->search != '') {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('nombre', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('apellidos', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('telefono', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('notas', 'LIKE', "%{$searchTerm}%");
            });
        }

        $publicadores = $query->orderBy('nombre')
            ->orderBy('apellidos')
            ->get();

        return view('publicadores.index', compact('publicadores'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!auth()->user()->canEditPublicadores()) {
            abort(403, 'No tienes permisos para crear publicadores.');
        }

        $grupos = GrupoPredicacion::where('congregacion_id', session('congregacion_activa_id'))
            ->orderBy('numero')
            ->get();

        return view('publicadores.create', compact('grupos'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!auth()->user()->canEditPublicadores()) {
            abort(403, 'No tienes permisos para crear publicadores.');
        }

        $request->validate([
            'nombre' => 'required|string|max:255',
            'apellidos' => 'nullable|string|max:255',
            'telefono' => 'required|string|max:20',
            'notas' => 'nullable|string|max:1000',
            'activo' => 'boolean',
            'es_menor' => 'boolean',
            'es_anciano' => 'boolean',
            'es_siervo_ministerial' => 'boolean',
            'es_precursor' => 'boolean',
            'aprobado_ppoc' => 'boolean',
            'grupo_predicacion_id' => 'nullable|exists:grupos_predicacion,id'
        ]);

        $esAnciano = $request->boolean('es_anciano');
        $esSm = $request->boolean('es_siervo_ministerial');
        $nombramientos = [];
        foreach (array_keys(Publicador::NOMBRAMIENTOS_CUERPO) as $campo) {
            $nombramientos[$campo] = $esAnciano ? $request->boolean($campo) : false;
        }
        $cargosSm = [];
        foreach (Publicador::CARGOS_SM_CATEGORIAS as $cat) {
            $cargosSm[$cat['titular']] = $esSm ? $request->boolean($cat['titular']) : false;
            $cargosSm[$cat['aux']] = $esSm ? $request->boolean($cat['aux']) : false;
        }

        $publicador = Publicador::create(array_merge([
            'nombre' => $request->nombre,
            'apellidos' => $request->apellidos,
            'telefono' => $request->telefono,
            'notas' => $request->notas,
            'activo' => $request->boolean('activo', true),
            'es_menor' => $request->boolean('es_menor'),
            'es_anciano' => $esAnciano,
            'es_siervo_ministerial' => $esSm,
            'es_precursor' => $request->boolean('es_precursor'),
            'grupo_predicacion_id' => $request->grupo_predicacion_id
        ], $nombramientos, $cargosSm));

        // Asegurar unicidad de los titulares SM en la congregacion
        $this->aplicarUnicidadTitularesSm($publicador);

        return redirect()->route('publicadores.index')
            ->with('success', 'Publicador creado exitosamente.');
    }

    /**
     * Si el publicador esta marcado como titular de algun cargo SM (es_siervo_X),
     * quitar ese mismo titular a cualquier otro publicador de la congregacion.
     */
    private function aplicarUnicidadTitularesSm(Publicador $publicador): void
    {
        foreach (array_keys(Publicador::CARGOS_SM_TITULARES) as $campo) {
            if ($publicador->{$campo}) {
                Publicador::where('congregacion_id', $publicador->congregacion_id)
                    ->where('id', '!=', $publicador->id)
                    ->where($campo, true)
                    ->update([$campo => false]);
            }
        }
    }

    /**
     * Display the specified resource
     */
    public function show(Publicador $publicador)
    {
        // Cargar relacion de grupo
        $publicador->load('grupoPredicacion');

        // Registro activo y territorio actual
        $registroActivo = $publicador->registros()
            ->whereNull('fecha_entrada')
            ->with('territorio')
            ->first();
        $publicador->territorio_actual = $registroActivo ? $registroActivo->territorio : null;

        // Cargar todos los registros para estadisticas
        $todosRegistros = $publicador->registros()
            ->with('territorio')
            ->orderBy('fecha_salida', 'desc')
            ->get();

        // Estadisticas generales
        $completados = $todosRegistros->whereNotNull('fecha_entrada');
        $activos = $todosRegistros->whereNull('fecha_entrada')->count();

        $totalDias = 0;
        foreach ($completados as $reg) {
            $totalDias += Carbon::parse($reg->fecha_salida)->diffInDays(Carbon::parse($reg->fecha_entrada));
        }

        $estadisticas = [
            'total' => $todosRegistros->count(),
            'activos' => $activos,
            'completados' => $completados->count(),
            'promedio_dias' => $completados->count() > 0 ? round($totalDias / $completados->count()) : 0,
        ];

        // Ano de servicio actual
        $anoServicio = $this->calcularAnoServicio();
        $fechasAno = $this->getFechasAnoServicio($anoServicio);

        // Registros del ano de servicio
        $registrosAno = $todosRegistros->filter(function($reg) use ($fechasAno) {
            return Carbon::parse($reg->fecha_salida)->between($fechasAno['inicio'], $fechasAno['fin']);
        });

        $completadosAno = $registrosAno->whereNotNull('fecha_entrada');
        $diasAno = 0;
        foreach ($completadosAno as $reg) {
            $diasAno += Carbon::parse($reg->fecha_salida)->diffInDays(Carbon::parse($reg->fecha_entrada));
        }
        // Sumar dias de territorios activos en el ano
        foreach ($registrosAno->whereNull('fecha_entrada') as $reg) {
            $diasAno += Carbon::parse($reg->fecha_salida)->diffInDays(now());
        }

        $estadisticasAno = [
            'territorios' => $registrosAno->count(),
            'completados' => $completadosAno->count(),
            'dias_servicio' => $diasAno,
        ];

        // Ultimos 5 registros
        $ultimosRegistros = $todosRegistros->take(5);

        // Grupos para el selector de edicion
        $grupos = GrupoPredicacion::where('congregacion_id', session('congregacion_activa_id'))
            ->orderBy('numero')
            ->get();

        return view('publicadores.show', compact(
            'publicador',
            'estadisticas',
            'estadisticasAno',
            'anoServicio',
            'ultimosRegistros',
            'grupos'
        ));
    }

    /**
     * Calcula el ano de servicio (septiembre a agosto)
     */
    private function calcularAnoServicio($fecha = null)
    {
        $fecha = $fecha ?? Carbon::now();
        $mes = $fecha->month;
        $ano = $fecha->year;

        // Si estamos entre enero y agosto, el ano de servicio empezo en septiembre del ano anterior
        if ($mes < 9) {
            return ($ano - 1) . '-' . $ano;
        }
        // Si estamos entre septiembre y diciembre, el ano de servicio es este ano al siguiente
        return $ano . '-' . ($ano + 1);
    }

    /**
     * Obtiene las fechas de inicio y fin de un ano de servicio
     */
    private function getFechasAnoServicio($anoServicio)
    {
        $anos = explode('-', $anoServicio);
        $anoInicio = (int)$anos[0];
        $anoFin = (int)$anos[1];

        return [
            'inicio' => Carbon::create($anoInicio, 9, 1, 0, 0, 0),
            'fin' => Carbon::create($anoFin, 8, 31, 23, 59, 59)
        ];
    }

    /**
     * Genera lista de anos de servicio disponibles
     */
    private function getAnosServicioDisponibles()
    {
        $anoActual = Carbon::now()->year;
        $mesActual = Carbon::now()->month;

        $anos = [];

        // Ano de servicio actual
        if ($mesActual >= 9) {
            $anos[] = $anoActual . '-' . ($anoActual + 1);
        } else {
            $anos[] = ($anoActual - 1) . '-' . $anoActual;
        }

        // Anos anteriores (hasta 5 anos atras)
        for ($i = 1; $i <= 5; $i++) {
            $inicio = $mesActual >= 9 ? $anoActual - $i : $anoActual - 1 - $i;
            $anos[] = $inicio . '-' . ($inicio + 1);
        }

        return $anos;
    }

    /**
     * Gestion de registros y territorios del publicador con filtros
     */
    public function registros(Request $request, Publicador $publicador)
    {
        // Solo admins pueden ver estadísticas completas
        if (!auth()->user()->canViewPublicadorStats()) {
            abort(403, 'No tienes permisos para ver las estadísticas de publicadores.');
        }

        // Obtener parametros de filtro
        $tipoFiltro = $request->get('filtro', 'todos'); // todos, ano_servicio, fechas
        $anoServicio = $request->get('ano_servicio', $this->calcularAnoServicio());
        $fechaInicio = $request->get('fecha_inicio');
        $fechaFin = $request->get('fecha_fin');

        // Cargar todos los registros
        $todosRegistros = $publicador->registros()
            ->with('territorio')
            ->orderBy('fecha_salida', 'desc')
            ->get();

        // Aplicar filtros
        $registrosFiltrados = $todosRegistros;
        $periodoTexto = 'Historico completo';
        $fechaInicioFiltro = null;
        $fechaFinFiltro = null;

        if ($tipoFiltro === 'ano_servicio') {
            $fechas = $this->getFechasAnoServicio($anoServicio);
            $fechaInicioFiltro = $fechas['inicio'];
            $fechaFinFiltro = $fechas['fin'];
            $periodoTexto = "Ano de servicio $anoServicio (Sep-Ago)";

            $registrosFiltrados = $todosRegistros->filter(function($registro) use ($fechaInicioFiltro, $fechaFinFiltro) {
                $fechaSalida = Carbon::parse($registro->fecha_salida);
                return $fechaSalida->between($fechaInicioFiltro, $fechaFinFiltro);
            });
        } elseif ($tipoFiltro === 'fechas' && $fechaInicio && $fechaFin) {
            $fechaInicioFiltro = Carbon::parse($fechaInicio)->startOfDay();
            $fechaFinFiltro = Carbon::parse($fechaFin)->endOfDay();
            $periodoTexto = $fechaInicioFiltro->format('d/m/Y') . ' - ' . $fechaFinFiltro->format('d/m/Y');

            $registrosFiltrados = $todosRegistros->filter(function($registro) use ($fechaInicioFiltro, $fechaFinFiltro) {
                $fechaSalida = Carbon::parse($registro->fecha_salida);
                return $fechaSalida->between($fechaInicioFiltro, $fechaFinFiltro);
            });
        }

        // Calcular estadisticas globales (sin filtro)
        $estadisticasGlobales = $this->calcularEstadisticas($todosRegistros);

        // Calcular estadisticas filtradas
        $estadisticasFiltradas = $this->calcularEstadisticas($registrosFiltrados);
        $estadisticasFiltradas['periodo'] = $periodoTexto;
        $estadisticasFiltradas['tiene_filtro'] = ($tipoFiltro !== 'todos');

        // Obtener territorio actual (siempre del total)
        $estadisticasFiltradas['territorio_actual'] = $todosRegistros->where('fecha_entrada', null)->first();

        // Anos de servicio disponibles
        $anosServicio = $this->getAnosServicioDisponibles();

        return view('publicadores.registros', [
            'publicador' => $publicador,
            'registros' => $registrosFiltrados,
            'registrosTotales' => $todosRegistros,
            'estadisticas' => $estadisticasFiltradas,
            'estadisticasGlobales' => $estadisticasGlobales,
            'anosServicio' => $anosServicio,
            'filtroActivo' => $tipoFiltro,
            'anoServicioSeleccionado' => $anoServicio,
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin
        ]);
    }

    /**
     * Calcula estadisticas de una coleccion de registros
     */
    private function calcularEstadisticas($registros)
    {
        $completados = $registros->where('fecha_entrada', '!=', null);

        $estadisticas = [
            'total_territorios' => $registros->count(),
            'territorios_completados' => $completados->count(),
            'promedio_dias' => 0,
            'total_dias_trabajados' => 0,
            'territorio_actual' => null
        ];

        if ($completados->count() > 0) {
            $totalDias = $completados->sum(function ($registro) {
                return Carbon::parse($registro->fecha_salida)
                    ->diffInDays(Carbon::parse($registro->fecha_entrada));
            });
            $estadisticas['promedio_dias'] = round($totalDias / $completados->count(), 1);
            $estadisticas['total_dias_trabajados'] = $totalDias;
        }

        return $estadisticas;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Publicador $publicador)
    {
        if (!auth()->user()->canEditPublicadores()) {
            abort(403, 'No tienes permisos para editar publicadores.');
        }

        $request->validate([
            'nombre' => 'required|string|max:255',
            'apellidos' => 'nullable|string|max:255',
            'telefono' => 'required|string|max:20',
            'notas' => 'nullable|string|max:1000',
            'activo' => 'boolean',
            'es_menor' => 'boolean',
            'es_anciano' => 'boolean',
            'es_siervo_ministerial' => 'boolean',
            'es_precursor' => 'boolean',
            'aprobado_ppoc' => 'boolean',
            'grupo_predicacion_id' => 'nullable|exists:grupos_predicacion,id'
        ]);

        $esAnciano = $request->boolean('es_anciano');
        $esSm = $request->boolean('es_siervo_ministerial');
        $nombramientos = [];
        foreach (array_keys(Publicador::NOMBRAMIENTOS_CUERPO) as $campo) {
            $nombramientos[$campo] = $esAnciano ? $request->boolean($campo) : false;
        }
        $cargosSm = [];
        foreach (Publicador::CARGOS_SM_CATEGORIAS as $cat) {
            $cargosSm[$cat['titular']] = $esSm ? $request->boolean($cat['titular']) : false;
            $cargosSm[$cat['aux']] = $esSm ? $request->boolean($cat['aux']) : false;
        }

        $publicador->update(array_merge([
            'nombre' => $request->nombre,
            'apellidos' => $request->apellidos,
            'telefono' => $request->telefono,
            'notas' => $request->notas,
            'activo' => $request->boolean('activo'),
            'es_menor' => $request->boolean('es_menor'),
            'es_anciano' => $esAnciano,
            'es_siervo_ministerial' => $esSm,
            'es_precursor' => $request->boolean('es_precursor'),
            'aprobado_ppoc' => $request->boolean('aprobado_ppoc'),
            'grupo_predicacion_id' => $request->grupo_predicacion_id
        ], $nombramientos, $cargosSm));

        $this->aplicarUnicidadTitularesSm($publicador);

        return redirect()->route('publicadores.show', $publicador)
            ->with('success', 'Publicador actualizado exitosamente.');
    }

    /**
     * Toggle precursor status
     */
    public function togglePrecursor(Publicador $publicador)
    {
        if (!auth()->user()->canEditPublicadores()) {
            return response()->json(["success" => false, "message" => "Sin permisos"], 403);
        }

        $publicador->update(["es_precursor" => !$publicador->es_precursor]);

        return response()->json([
            "success" => true,
            "es_precursor" => $publicador->es_precursor
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Publicador $publicador)
    {
        if (!auth()->user()->canEditPublicadores()) {
            abort(403, 'No tienes permisos para eliminar publicadores.');
        }

        $registroActivo = $publicador->registros()->whereNull('fecha_entrada')->first();

        if ($registroActivo) {
            return redirect()->route('publicadores.index')
                ->with('error', 'No se puede eliminar un publicador con territorios asignados.');
        }

        $publicador->delete();

        return redirect()->route('publicadores.index')
            ->with('success', 'Publicador eliminado exitosamente.');
    }

    /**
     * Get family members for a publicador
     */
    public function familiares(Publicador $publicador)
    {
        $relaciones = \App\Models\RelacionFamiliar::where('publicador_id', $publicador->id)
            ->with('familiar:id,nombre,apellidos')
            ->get();

        $familiares = $relaciones->map(function($rel) {
            return [
                'familiar' => $rel->familiar,
                'tipo_relacion' => $rel->tipo_relacion,
                'tipo_label' => \App\Models\RelacionFamiliar::TIPOS[$rel->tipo_relacion] ?? $rel->tipo_relacion,
            ];
        });

        return response()->json(['familiares' => $familiares]);
    }

    /**
     * Get available publishers for family relationships
     */
    public function disponiblesFamilia(Publicador $publicador)
    {
        $familiarIds = \App\Models\RelacionFamiliar::where('publicador_id', $publicador->id)
            ->pluck('familiar_id')
            ->toArray();

        $disponibles = Publicador::where('congregacion_id', $publicador->congregacion_id)
            ->where('id', '!=', $publicador->id)
            ->whereNotIn('id', $familiarIds)
            ->orderBy('apellidos')
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'apellidos']);

        return response()->json(['disponibles' => $disponibles]);
    }

    /**
     * Add a family relationship
     */
    public function addFamiliar(Request $request, Publicador $publicador)
    {
        $request->validate([
            'familiar_id' => 'required|exists:publicadores,id',
            'tipo_relacion' => 'required|in:conyuge,progenitor,hijo',
        ]);

        try {
            \App\Models\RelacionFamiliar::crearRelacion(
                $publicador->id,
                $request->familiar_id,
                $request->tipo_relacion
            );
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * Remove a family relationship
     */
    public function removeFamiliar(Request $request, Publicador $publicador)
    {
        $request->validate([
            'familiar_id' => 'required|exists:publicadores,id',
        ]);

        \App\Models\RelacionFamiliar::eliminarRelacion($publicador->id, $request->familiar_id);
        return response()->json(['success' => true]);
    }

}
