<?php

namespace App\Http\Controllers;

use App\Models\Turno;
use App\Models\TurnoGenerado;
use App\Models\TurnoAsignacion;
use App\Models\Publicador;
use App\Models\Congregacion;
use Illuminate\Http\Request;
use Carbon\Carbon;

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

        return view('ppoc.calendario', compact(
            'turnosGenerados', 'plantillas', 'publicadores',
            'year', 'month', 'semanas', 'nombreMes', 'prevMonth', 'nextMonth',
            'mesGenerado'
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

        return redirect()->back()->with("success", "{$publicador->nombre_completo} ha sido {$estado} para PPOC.");
    }
}