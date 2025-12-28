<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Publicador;
use App\Models\Registro;
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
     * Display the specified resource
     */
    public function show(Publicador $publicador)
    {
        $registroActivo = $publicador->registros()
            ->whereNull('fecha_entrada')
            ->with('territorio')
            ->first();
        $publicador->territorio_actual = $registroActivo ? $registroActivo->territorio : null;

        return view('publicadores.show', compact('publicador'));
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
