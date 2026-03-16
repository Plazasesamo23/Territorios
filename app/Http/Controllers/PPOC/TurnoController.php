<?php

namespace App\Http\Controllers\PPOC;

use App\Http\Controllers\Controller;

use App\Models\Turno;
use App\Models\TurnoGenerado;
use App\Models\TurnoAsignacion;
use App\Models\Publicador;
use App\Models\Congregacion;
use App\Services\AsignacionPpocService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;

class TurnoController extends Controller
{
    /**
     * Verificar acceso al módulo PPOC
     */
    private function checkAccess()
    {
        if (!auth()->user()->canAccessPPOC()) {
            abort(403, 'No tienes permisos para acceder al módulo PPOC.');
        }
    }

    /**
     * Obtener la congregación activa
     */
    private function getCongregacion()
    {
        $congregacionId = session('congregacion_activa_id');
        return Congregacion::find($congregacionId);
    }

    /**
     * Mostrar lista de plantillas de turnos semanales
     */
    public function index()
    {
        $this->checkAccess();

        $congregacion = $this->getCongregacion();
        $turnos = Turno::where('congregacion_id', $congregacion->id)
            ->orderBy('dia_semana')
            ->orderBy('numero_turno')
            ->orderBy('hora_inicio')
            ->get();

        return view('ppoc.turnos.index', compact('turnos'));
    }

    /**
     * Mostrar formulario para crear plantilla de turno
     */
    public function create(Request $request)
    {
        $this->checkAccess();

        $turnoBase = null;
        if ($request->has('copiar')) {
            $turnoBase = Turno::find($request->get('copiar'));
        }

        return view('ppoc.turnos.create', [
            'diasSemana' => Turno::DIAS_SEMANA,
            'tipos' => Turno::TIPOS,
            'turnoBase' => $turnoBase,
        ]);
    }

    /**
     * Guardar nueva plantilla de turno
     */
    public function store(Request $request)
    {
        $this->checkAccess();

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'dia_semana' => 'required|integer|min:0|max:6',
            'numero_turno' => 'required|integer|min:1|max:2',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
            'ubicacion' => 'nullable|string|max:255',
            'tipo' => 'required|string|in:' . implode(',', array_keys(Turno::TIPOS)),
            'capacidad' => 'required|integer|min:1|max:20',
            'notas' => 'nullable|string',
        ]);

        $congregacion = $this->getCongregacion();
        $validated['congregacion_id'] = $congregacion->id;
        $validated['activo'] = true;

        Turno::create($validated);

        return redirect()->route('ppoc.turnos.index')
            ->with('success', 'Plantilla de turno creada correctamente.');
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit(Turno $turno)
    {
        $this->checkAccess();

        return view('ppoc.turnos.edit', [
            'turno' => $turno,
            'diasSemana' => Turno::DIAS_SEMANA,
            'tipos' => Turno::TIPOS,
        ]);
    }

    /**
     * Actualizar plantilla de turno
     */
    public function update(Request $request, Turno $turno)
    {
        $this->checkAccess();

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'dia_semana' => 'required|integer|min:0|max:6',
            'numero_turno' => 'required|integer|min:1|max:2',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
            'ubicacion' => 'nullable|string|max:255',
            'tipo' => 'required|string|in:' . implode(',', array_keys(Turno::TIPOS)),
            'capacidad' => 'required|integer|min:1|max:20',
            'notas' => 'nullable|string',
            'activo' => 'boolean',
        ]);

        $turno->update($validated);

        return redirect()->route('ppoc.turnos.index')
            ->with('success', 'Plantilla de turno actualizada.');
    }

    /**
     * Eliminar plantilla de turno
     */
    public function destroy(Turno $turno)
    {
        $this->checkAccess();

        $turno->delete();

        return redirect()->route('ppoc.turnos.index')
            ->with('success', 'Plantilla de turno eliminada.');
    }

    /**
     * Vista del calendario mensual de asignaciones
     */
    public function calendario(Request $request)
    {
        $this->checkAccess();

        $congregacion = $this->getCongregacion();
        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);

        // Obtener turnos generados del mes
        $turnosGenerados = TurnoGenerado::with(['turno', 'asignaciones.publicador'])
            ->where('congregacion_id', $congregacion->id)
            ->delMes($year, $month)
            ->orderBy('fecha')
            ->get()
            ->groupBy(function($tg) {
                return $tg->fecha->format('Y-m-d');
            });

        // Obtener plantillas de turnos activas
        $plantillas = Turno::where('congregacion_id', $congregacion->id)
            ->where('activo', true)
            ->orderBy('dia_semana')
            ->orderBy('numero_turno')
            ->get();

        // Solo publicadores aprobados para PPOC
        $publicadores = Publicador::where('congregacion_id', $congregacion->id)
            ->where('activo', true)
            ->where('aprobado_ppoc', true)
            ->orderBy('nombre')
            ->get();

        // Generar estructura del calendario
        $primerDia = Carbon::create($year, $month, 1);
        $ultimoDia = $primerDia->copy()->endOfMonth();
        $hoy = Carbon::today();
        $semanas = [];
        $semanaActual = [];

        // Rellenar días vacíos al inicio (días del mes anterior)
        // dayOfWeekIso: 1=Lunes, 7=Domingo (formato europeo)
        $diasVacios = $primerDia->dayOfWeekIso - 1;
        for ($i = 0; $i < $diasVacios; $i++) {
            $semanaActual[] = null;
        }

        // Agregar días del mes como arrays con propiedades
        $dia = $primerDia->copy();
        while ($dia <= $ultimoDia) {
            $fechaStr = $dia->format('Y-m-d');
            $turnosDelDia = $turnosGenerados->get($fechaStr, collect());

            $semanaActual[] = [
                'fecha' => $dia->copy(),
                'numero' => $dia->day,
                'esHoy' => $dia->isSameDay($hoy),
                'esMesActual' => true,
                'turnos' => $turnosDelDia,
            ];

            if (count($semanaActual) === 7) {
                $semanas[] = $semanaActual;
                $semanaActual = [];
            }
            $dia->addDay();
        }

        // Rellenar días vacíos al final
        while (count($semanaActual) > 0 && count($semanaActual) < 7) {
            $semanaActual[] = null;
        }
        if (!empty($semanaActual)) {
            $semanas[] = $semanaActual;
        }

        $nombreMes = Carbon::create($year, $month, 1)->translatedFormat("F");
        $prevMonth = Carbon::create($year, $month, 1)->subMonth();
        $nextMonth = Carbon::create($year, $month, 1)->addMonth();

        // Verificar si el mes ya está generado
        $mesGenerado = $turnosGenerados->isNotEmpty();

        // Obtener alertas y estadisticas del servicio
        $service = new AsignacionPpocService($congregacion->id);
        $alertasData = $service->getAlertas($year, $month);

        return view('ppoc.calendario', compact(
            'turnosGenerados', 'plantillas', 'publicadores',
            'year', 'month', 'semanas', 'nombreMes', 'prevMonth', 'nextMonth',
            'mesGenerado', 'alertasData'
        ));
    }

    /**
     * Generar turnos para un mes basándose en las plantillas
     */
    public function generarMes(Request $request)
    {
        $this->checkAccess();

        $validated = $request->validate([
            'year' => 'required|integer|min:2024|max:2100',
            'month' => 'required|integer|min:1|max:12',
        ]);

        $congregacion = $this->getCongregacion();
        $year = $validated['year'];
        $month = $validated['month'];

        $plantillas = Turno::where('congregacion_id', $congregacion->id)
            ->where('activo', true)
            ->get();

        $creados = 0;
        foreach ($plantillas as $plantilla) {
            $creados += $plantilla->generarParaMes($year, $month);
        }

        return redirect()->route('ppoc.calendario', ['year' => $year, 'month' => $month])
            ->with('success', "Calendario generado: {$creados} turnos creados.");
    }

    /**
     * Asignar publicadores automaticamente para un mes
     * Usa el sistema de medias: precursores no pueden tener menos turnos que la media de publicadores
     * y publicadores no pueden tener mas turnos que la media de precursores
     */
    
    /**
     * Limpiar todas las asignaciones de un mes
     */
    public function limpiarMes(Request $request)
    {
        $this->checkAccess();

        $validated = $request->validate([
            "year" => "required|integer|min:2024|max:2100",
            "month" => "required|integer|min:1|max:12",
        ]);

        $congregacion = $this->getCongregacion();
        $year = $validated["year"];
        $month = $validated["month"];

        // Obtener todos los turnos generados del mes
        $turnosGeneradosIds = TurnoGenerado::where("congregacion_id", $congregacion->id)
            ->whereYear("fecha", $year)
            ->whereMonth("fecha", $month)
            ->pluck("id");

        // Borrar todas las asignaciones de esos turnos
        $eliminados = TurnoAsignacion::whereIn("turno_generado_id", $turnosGeneradosIds)->delete();

        return redirect()->route("ppoc.calendario", ["year" => $year, "month" => $month])
            ->with("success", "Se han eliminado {$eliminados} asignaciones del mes.");
    }

    /**
     * Asignar publicadores automaticamente para un mes
     */
    public function asignacionAutomatica(Request $request)
    {
        $this->checkAccess();

        $validated = $request->validate([
            'year' => 'required|integer|min:2024|max:2100',
            'month' => 'required|integer|min:1|max:12',
        ]);

        $congregacion = $this->getCongregacion();
        $year = $validated['year'];
        $month = $validated['month'];

        $service = new AsignacionPpocService($congregacion->id);
        $resultado = $service->generarMes($year, $month);

        $mensaje = "Asignacion automatica completada: {$resultado['asignados']} turnos asignados.";

        if ($resultado['incompletos'] > 0) {
            $mensaje .= " {$resultado['incompletos']} turnos quedaron incompletos.";
        }

        if (!empty($resultado['errores'])) {
            return redirect()->route('ppoc.calendario', ['year' => $year, 'month' => $month])
                ->with('error', implode(' ', $resultado['errores']));
        }

        return redirect()->route('ppoc.calendario', ['year' => $year, 'month' => $month])
            ->with('success', $mensaje);
    }

    /**
     * Asignar publicador a un turno generado
     */
    public function asignar(Request $request)
    {
        $this->checkAccess();

        $validated = $request->validate([
            'turno_generado_id' => 'required|exists:turnos_generados,id',
            'publicador_id' => 'required|exists:publicadores,id',
            'rol' => 'required|in:capitan,voluntario',
        ]);

        $turnoGenerado = TurnoGenerado::findOrFail($validated['turno_generado_id']);
        $publicador = Publicador::findOrFail($validated['publicador_id']);

        // Verificar que el publicador esté aprobado para PPOC
        if (!$publicador->aprobado_ppoc) {
            return back()->with('error', 'Este publicador no está aprobado para participar en PPOC.');
        }

        // Verificar capacidad
        if (!$turnoGenerado->tieneEspacioDisponible()) {
            return back()->with('error', 'Este turno ya está completo.');
        }

        // Verificar que el publicador no esté ya asignado
        $yaAsignado = TurnoAsignacion::where('turno_generado_id', $validated['turno_generado_id'])
            ->where('publicador_id', $validated['publicador_id'])
            ->exists();

        if ($yaAsignado) {
            return back()->with('error', 'Este publicador ya está asignado a este turno.');
        }

        // Verificar que solo haya un capitán
        if ($validated['rol'] === 'capitan') {
            $hayCapitan = TurnoAsignacion::where('turno_generado_id', $validated['turno_generado_id'])
                ->where('rol', 'capitan')
                ->exists();

            if ($hayCapitan) {
                return back()->with('error', 'Ya hay un capitán asignado a este turno.');
            }
        }

        TurnoAsignacion::create([
            'turno_id' => $turnoGenerado->turno_id,
            'turno_generado_id' => $validated['turno_generado_id'],
            'publicador_id' => $validated['publicador_id'],
            'fecha' => $turnoGenerado->fecha,
            'rol' => $validated['rol'],
            'estado' => 'pendiente',
        ]);

        // Actualizar estado del turno si está completo
        if ($turnoGenerado->estaCompleto()) {
            $turnoGenerado->update(['estado' => 'completo']);
        }

        return back()->with('success', 'Publicador asignado correctamente.');
    }

    /**
     * Quitar asignación
     */
    public function desasignar(TurnoAsignacion $asignacion)
    {
        $this->checkAccess();

        $turnoGenerado = $asignacion->turnoGenerado;
        $asignacion->delete();

        // Reabrir turno si estaba completo
        if ($turnoGenerado && $turnoGenerado->estado === 'completo') {
            $turnoGenerado->update(['estado' => 'abierto']);
        }

        return back()->with('success', 'Asignación eliminada.');
    }

    /**
     * Actualizar estado de asignación
     */
    public function actualizarEstado(Request $request, TurnoAsignacion $asignacion)
    {
        $validated = $request->validate([
            'estado' => 'required|in:pendiente,confirmado,completado,cancelado',
        ]);

        $asignacion->update(['estado' => $validated['estado']]);

        return back()->with('success', 'Estado actualizado.');
    }

    /**
     * Eliminar un turno generado especifico (ej: dia festivo)
     */
    public function destroyTurnoGenerado(TurnoGenerado $turnoGenerado)
    {
        $this->checkAccess();

        $fecha = $turnoGenerado->fecha->format('d/m/Y');
        $hora = Carbon::parse($turnoGenerado->hora_inicio)->format('H:i');

        // Eliminar asignaciones asociadas primero
        $turnoGenerado->asignaciones()->delete();

        // Eliminar el turno generado
        $turnoGenerado->delete();

        return back()->with('success', "Turno del {$fecha} a las {$hora} eliminado.");
    }


    /**
     * Mostrar lista de publicadores para aprobar/desaprobar PPOC
     */
    public function aprobados()
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        $congregacionId = session("congregacion_activa_id");

        $publicadores = Publicador::where("congregacion_id", $congregacionId)
            ->where("activo", true)
            ->orderBy("aprobado_ppoc", "desc")
            ->orderBy("nombre")
            ->orderBy("apellidos")
            ->get();

        $stats = [
            "total" => $publicadores->count(),
            "aprobados" => $publicadores->where("aprobado_ppoc", true)->count(),
            "capitanes" => $publicadores->where("es_capitan_ppoc", true)->count(),
            "pendientes" => $publicadores->where("aprobado_ppoc", false)->count(),
        ];

        return view("ppoc.aprobados", compact("publicadores", "stats"));
    }

    /**
     * Toggle estado aprobado de un publicador
     */
    public function toggleAprobado(Publicador $publicador)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        $publicador->aprobado_ppoc = !$publicador->aprobado_ppoc;
        $publicador->save();

        $estado = $publicador->aprobado_ppoc ? "aprobado" : "removido";

        // Si se remueve de PPOC, también quitar como capitán
        if (!$publicador->aprobado_ppoc) {
            $publicador->es_capitan_ppoc = false;
            $publicador->save();
        }

        return redirect()->back()->with("success", "{$publicador->nombre_completo} ha sido {$estado} para PPOC.");
    }

    /**
     * Toggle estado capitan de un publicador
     */
    public function toggleCapitan(Publicador $publicador)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        // Solo puede ser capitan si está aprobado y no es menor
        if (!$publicador->aprobado_ppoc) {
            return redirect()->back()->with("error", "El publicador debe estar aprobado para PPOC primero.");
        }

        if ($publicador->es_menor) {
            return redirect()->back()->with("error", "Los menores de edad no pueden ser capitanes.");
        }

        $publicador->es_capitan_ppoc = !$publicador->es_capitan_ppoc;
        $publicador->save();

        $estado = $publicador->es_capitan_ppoc ? "asignado como capitan" : "removido como capitan";

        return redirect()->back()->with("success", "{$publicador->nombre_completo} ha sido {$estado}.");
    }

    /**
     * Obtener sugerencias de reemplazo para una asignacion
     * Devuelve publicadores disponibles ordenados por idoneidad
     */
    public function getSugerencias(TurnoAsignacion $asignacion)
    {
        $this->checkAccess();

        $turnoGenerado = $asignacion->turnoGenerado;
        $turnoPlantilla = $turnoGenerado->turno;
        $congregacionId = session('congregacion_activa_id');
        $rolBuscado = $asignacion->rol;

        // Obtener IDs de publicadores ya asignados a este turno
        $asignadosIds = TurnoAsignacion::where('turno_generado_id', $turnoGenerado->id)
            ->pluck('publicador_id')
            ->toArray();

        // Obtener publicadores disponibles para este turno (por plantilla)
        $disponiblesIds = \App\Models\DisponibilidadPpoc::where('turno_id', $turnoPlantilla->id)
            ->pluck('publicador_id')
            ->toArray();

        // Obtener publicadores aprobados que estan disponibles y no estan ya asignados
        $query = Publicador::where('congregacion_id', $congregacionId)
            ->where('activo', true)
            ->where('aprobado_ppoc', true)
            ->whereIn('id', $disponiblesIds)
            ->whereNotIn('id', $asignadosIds);

        // Si buscamos capitan, filtrar solo capitanes
        if ($rolBuscado === 'capitan') {
            $query->where('es_capitan_ppoc', true);
        }

        $candidatos = $query->get();

        // Contar turnos asignados este mes para cada candidato
        $year = $turnoGenerado->fecha->year;
        $month = $turnoGenerado->fecha->month;

        $turnosPorPublicador = TurnoAsignacion::whereHas('turnoGenerado', function($q) use ($congregacionId, $year, $month) {
                $q->where('congregacion_id', $congregacionId)
                    ->whereYear('fecha', $year)
                    ->whereMonth('fecha', $month);
            })
            ->selectRaw('publicador_id, COUNT(*) as total')
            ->groupBy('publicador_id')
            ->pluck('total', 'publicador_id')
            ->toArray();

        // Preparar respuesta con puntuacion
        $sugerencias = $candidatos->map(function($pub) use ($turnosPorPublicador, $rolBuscado) {
            $turnos = $turnosPorPublicador[$pub->id] ?? 0;

            return [
                'id' => $pub->id,
                'nombre' => $pub->nombre,
                'apellidos' => $pub->apellidos,
                'nombre_completo' => $pub->nombre_completo,
                'es_capitan' => $pub->es_capitan_ppoc,
                'es_precursor' => $pub->es_precursor,
                'turnos_mes' => $turnos,
            ];
        });

        // Ordenar: menos turnos primero, precursores primero si empatan
        $sugerencias = $sugerencias->sortBy([
            ['turnos_mes', 'asc'],
            ['es_precursor', 'desc'],
        ])->values();

        return response()->json([
            'asignacion_id' => $asignacion->id,
            'rol' => $rolBuscado,
            'turno_fecha' => $turnoGenerado->fecha->format('d/m/Y'),
            'turno_hora' => substr($turnoGenerado->hora_inicio, 0, 5),
            'publicador_actual' => $asignacion->publicador->nombre_completo,
            'sugerencias' => $sugerencias,
        ]);
    }

    /**
     * Reemplazar un publicador en una asignacion
     */
    public function reemplazar(Request $request, TurnoAsignacion $asignacion)
    {
        $this->checkAccess();

        $validated = $request->validate([
            'nuevo_publicador_id' => 'required|exists:publicadores,id',
        ]);

        $nuevoPublicador = Publicador::findOrFail($validated['nuevo_publicador_id']);
        $turnoGenerado = $asignacion->turnoGenerado;

        // Verificar que el nuevo publicador no este ya asignado
        $yaAsignado = TurnoAsignacion::where('turno_generado_id', $turnoGenerado->id)
            ->where('publicador_id', $nuevoPublicador->id)
            ->exists();

        if ($yaAsignado) {
            return back()->with('error', 'Este publicador ya esta asignado a este turno.');
        }

        // Si el rol es capitan, verificar que el nuevo publicador sea capitan
        if ($asignacion->rol === 'capitan' && !$nuevoPublicador->es_capitan_ppoc) {
            return back()->with('error', 'Solo los capitanes pueden reemplazar a un capitan.');
        }

        $nombreAnterior = $asignacion->publicador->nombre_completo;

        // Realizar el reemplazo
        $asignacion->publicador_id = $nuevoPublicador->id;
        $asignacion->save();

        return back()->with('success', "{$nombreAnterior} ha sido reemplazado por {$nuevoPublicador->nombre_completo}.");
    }

    /**
     * Exportar calendario del mes a PDF
     */
    public function exportarPdf(Request $request)
    {
        $this->checkAccess();

        $congregacion = $this->getCongregacion();
        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);

        // Obtener turnos generados del mes con sus asignaciones
        $turnosGenerados = TurnoGenerado::with(['turno', 'asignaciones.publicador'])
            ->where('congregacion_id', $congregacion->id)
            ->whereYear('fecha', $year)
            ->whereMonth('fecha', $month)
            ->where('estado', '!=', 'cancelado')
            ->orderBy('fecha')
            ->orderBy('hora_inicio')
            ->get();

        // Obtener horarios de las plantillas
        $plantillas = Turno::where('congregacion_id', $congregacion->id)
            ->where('activo', true)
            ->get();

        // Determinar horarios de mañana y tarde (según las plantillas)
        $horarioManana = '10:00-13:00';
        $horarioTarde = '17:30-19:30';
        $horarioSabado = '9:00-11:00 / 11:00-13:00';
        $horarioDomingo = '10:00-12:00';

        foreach ($plantillas as $p) {
            $hora = substr($p->hora_inicio, 0, 5);
            if ($hora < '14:00' && $p->dia_semana < 5) {
                $horarioManana = substr($p->hora_inicio, 0, 5) . '-' . substr($p->hora_fin, 0, 5);
            } elseif ($hora >= '14:00' && $p->dia_semana < 5) {
                $horarioTarde = substr($p->hora_inicio, 0, 5) . '-' . substr($p->hora_fin, 0, 5);
            }
        }

        // Calcular semanas del mes (agrupando de Lunes a Domingo)
        $primerDia = Carbon::create($year, $month, 1);
        $ultimoDia = $primerDia->copy()->endOfMonth();

        $semanas = [];
        $dia = $primerDia->copy();
        $numSemana = 1;
        $semanaActual = [
            'rango' => '',
            'dias' => [],
            'inicio' => $dia->day,
            'fin' => $dia->day
        ];

        while ($dia <= $ultimoDia) {
            // dayOfWeekIso: 1=Lunes, 7=Domingo -> convertir a 0=Lunes, 6=Domingo
            $diaIdx = $dia->dayOfWeekIso - 1;

            // Buscar turnos de este día
            $turnosDelDia = $turnosGenerados->filter(function($t) use ($dia) {
                return $t->fecha->isSameDay($dia);
            });

            // Separar en mañana y tarde (o turno 1 y turno 2 para sábado)
            $turnoManana = null;
            $turnoTarde = null;
            $turnosOrdenados = $turnosDelDia->sortBy('hora_inicio')->values();

            if ($turnosOrdenados->count() >= 2) {
                // Si hay 2 turnos, el primero es mañana/turno1, el segundo es tarde/turno2
                $turnoManana = [
                    'turno' => $turnosOrdenados[0],
                    'asignaciones' => $turnosOrdenados[0]->asignaciones
                ];
                $turnoTarde = [
                    'turno' => $turnosOrdenados[1],
                    'asignaciones' => $turnosOrdenados[1]->asignaciones
                ];
            } elseif ($turnosOrdenados->count() == 1) {
                $turno = $turnosOrdenados[0];
                $hora = substr($turno->hora_inicio, 0, 5);
                if ($hora < '14:00') {
                    $turnoManana = [
                        'turno' => $turno,
                        'asignaciones' => $turno->asignaciones
                    ];
                } else {
                    $turnoTarde = [
                        'turno' => $turno,
                        'asignaciones' => $turno->asignaciones
                    ];
                }
            }

            $semanaActual['dias'][$diaIdx] = [
                'fecha' => $dia->copy(),
                'manana' => $turnoManana,
                'tarde' => $turnoTarde
            ];
            $semanaActual['fin'] = $dia->day;

            // Si es Domingo (diaIdx=6) o último día del mes, cerrar semana
            if ($diaIdx == 6 || $dia->isSameDay($ultimoDia)) {
                $semanaActual['rango'] = sprintf('%02d AL %02d', $semanaActual['inicio'], $semanaActual['fin']);
                $semanas[$numSemana] = [
                    'rango' => $semanaActual['rango'],
                    'dias' => $semanaActual['dias']
                ];
                $numSemana++;

                // Preparar siguiente semana
                $semanaActual = [
                    'rango' => '',
                    'dias' => [],
                    'inicio' => $dia->day + 1,
                    'fin' => $dia->day + 1
                ];
            }

            $dia->addDay();
        }

        // Días de la semana
        $diasSemana = [
            0 => 'Lunes',
            1 => 'Martes',
            2 => 'Miercoles',
            3 => 'Jueves',
            4 => 'Viernes',
            5 => 'Sabado',
            6 => 'Domingo'
        ];

        // Nombre del mes en español
        $mesesEspanol = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];
        $nombreMes = $mesesEspanol[$month];

        // Renderizar vista a HTML
        $html = view('ppoc.pdf', compact(
            'semanas',
            'diasSemana',
            'congregacion',
            'year',
            'month',
            'nombreMes',
            'horarioManana',
            'horarioTarde',
            'horarioSabado',
            'horarioDomingo'
        ))->render();

        // Configurar Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $options->set('defaultFont', 'sans-serif');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $filename = "PPOC_" . strtoupper($nombreMes) . "_{$year}.pdf";

        return $dompdf->stream($filename, ['Attachment' => false]);
    }
}
