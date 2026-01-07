<?php

namespace App\Services;

use App\Models\Turno;
use App\Models\TurnoGenerado;
use App\Models\TurnoAsignacion;
use App\Models\Publicador;
use App\Models\DisponibilidadPpoc;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AsignacionPpocService
{
    private int $congregacionId;
    private array $historialParejas = [];
    private array $turnosAsignadosMes = [];

    public function __construct(int $congregacionId)
    {
        $this->congregacionId = $congregacionId;
    }

    /**
     * Generar asignaciones automaticas para un mes
     */
    public function generarMes(int $year, int $month): array
    {
        $resultados = [
            'asignados' => 0,
            'incompletos' => 0,
            'sin_capitan' => [],
            'sin_voluntarios' => [],
            'errores' => [],
        ];

        $turnosGenerados = TurnoGenerado::where('congregacion_id', $this->congregacionId)
            ->whereYear('fecha', $year)
            ->whereMonth('fecha', $month)
            ->where('estado', '!=', 'cancelado')
            ->orderBy('fecha')
            ->orderBy('hora_inicio')
            ->get();

        if ($turnosGenerados->isEmpty()) {
            $resultados['errores'][] = 'No hay turnos generados para este mes.';
            return $resultados;
        }

        $this->cargarHistorialParejas();
        $this->turnosAsignadosMes = [];

        foreach ($turnosGenerados as $turnoGenerado) {
            $asignacionesExistentes = TurnoAsignacion::where('turno_generado_id', $turnoGenerado->id)->count();
            if ($asignacionesExistentes >= 3) {
                continue;
            }

            if ($asignacionesExistentes > 0) {
                TurnoAsignacion::where('turno_generado_id', $turnoGenerado->id)->delete();
            }

            $resultado = $this->asignarTurno($turnoGenerado);

            if ($resultado['exito']) {
                $resultados['asignados']++;
            } else {
                $resultados['incompletos']++;
                if (!$resultado['tiene_capitan']) {
                    $resultados['sin_capitan'][] = $turnoGenerado->fecha->format('d/m') . ' ' . substr($turnoGenerado->hora_inicio, 0, 5);
                }
                if ($resultado['voluntarios'] < 2) {
                    $resultados['sin_voluntarios'][] = $turnoGenerado->fecha->format('d/m') . ' ' . substr($turnoGenerado->hora_inicio, 0, 5);
                }
            }
        }

        return $resultados;
    }

    private function asignarTurno(TurnoGenerado $turnoGenerado): array
    {
        $resultado = [
            'exito' => false,
            'tiene_capitan' => false,
            'voluntarios' => 0,
        ];

        $disponibles = $this->getPublicadoresDisponibles($turnoGenerado->turno_id);

        if ($disponibles->isEmpty()) {
            return $resultado;
        }

        $capitanes = $disponibles->filter(fn($p) => $p->es_capitan_ppoc);
        $voluntarios = $disponibles->filter(fn($p) => !$p->es_capitan_ppoc);

        $capitanSeleccionado = $this->seleccionarMejor($capitanes, [], $turnoGenerado);

        if (!$capitanSeleccionado) {
            return $resultado;
        }

        $resultado['tiene_capitan'] = true;
        $asignados = [$capitanSeleccionado->id];

        $this->crearAsignacion($turnoGenerado, $capitanSeleccionado, 'capitan');

        for ($i = 0; $i < 2; $i++) {
            $voluntarioSeleccionado = $this->seleccionarMejor($voluntarios, $asignados, $turnoGenerado);

            if ($voluntarioSeleccionado) {
                $asignados[] = $voluntarioSeleccionado->id;
                $this->crearAsignacion($turnoGenerado, $voluntarioSeleccionado, 'voluntario');
                $resultado['voluntarios']++;
                $voluntarios = $voluntarios->reject(fn($p) => $p->id === $voluntarioSeleccionado->id);
            }
        }

        $this->actualizarHistorialParejas($asignados);

        $resultado['exito'] = $resultado['tiene_capitan'] && $resultado['voluntarios'] >= 2;

        if ($resultado['exito']) {
            $turnoGenerado->update(['estado' => 'completo']);
        }

        return $resultado;
    }

    private function getPublicadoresDisponibles(int $turnoId): Collection
    {
        $publicadorIds = DisponibilidadPpoc::where('turno_id', $turnoId)->pluck('publicador_id');

        return Publicador::whereIn('id', $publicadorIds)
            ->where('congregacion_id', $this->congregacionId)
            ->where('activo', true)
            ->where('aprobado_ppoc', true)
            ->get();
    }

    private function seleccionarMejor(Collection $candidatos, array $yaAsignados, TurnoGenerado $turno): ?Publicador
    {
        if ($candidatos->isEmpty()) {
            return null;
        }

        $candidatos = $candidatos->reject(fn($p) => in_array($p->id, $yaAsignados));

        if ($candidatos->isEmpty()) {
            return null;
        }

        $puntuaciones = [];
        foreach ($candidatos as $candidato) {
            $puntuaciones[$candidato->id] = $this->calcularPuntuacion($candidato, $yaAsignados, $turno);
        }

        arsort($puntuaciones);

        $mejorId = array_key_first($puntuaciones);
        return $candidatos->firstWhere('id', $mejorId);
    }

    private function calcularPuntuacion(Publicador $publicador, array $companeros, TurnoGenerado $turno): float
    {
        $puntuacion = 100.0;

        if ($publicador->es_precursor) {
            $puntuacion += 30;
        }

        $turnosMes = $this->turnosAsignadosMes[$publicador->id] ?? 0;
        $puntuacion -= ($turnosMes * 15);

        foreach ($companeros as $companeroId) {
            if ($this->fueronJuntos($publicador->id, $companeroId)) {
                $puntuacion -= 20;
            }
        }

        if ($publicador->es_precursor) {
            $semana = $turno->fecha->weekOfYear;
            if (!$this->tieneTurnoSemana($publicador->id, $semana, $turno->fecha->year)) {
                $puntuacion += 25;
            }
        }

        $puntuacion += rand(-5, 5);

        return $puntuacion;
    }

    private function fueronJuntos(int $id1, int $id2): bool
    {
        $key = min($id1, $id2) . '-' . max($id1, $id2);
        return isset($this->historialParejas[$key]) && $this->historialParejas[$key] >= 2;
    }

    private function tieneTurnoSemana(int $publicadorId, int $semana, int $year): bool
    {
        return TurnoAsignacion::where('publicador_id', $publicadorId)
            ->whereYear('fecha', $year)
            ->get()
            ->filter(fn($a) => Carbon::parse($a->fecha)->weekOfYear === $semana)
            ->isNotEmpty();
    }

    private function cargarHistorialParejas(): void
    {
        $fechaLimite = Carbon::now()->subMonths(3);

        $asignaciones = TurnoAsignacion::where('fecha', '>=', $fechaLimite)
            ->whereHas('turnoGenerado', fn($q) => $q->where('congregacion_id', $this->congregacionId))
            ->get()
            ->groupBy('turno_generado_id');

        $this->historialParejas = [];

        foreach ($asignaciones as $turnoId => $grupo) {
            $ids = $grupo->pluck('publicador_id')->toArray();

            for ($i = 0; $i < count($ids); $i++) {
                for ($j = $i + 1; $j < count($ids); $j++) {
                    $key = min($ids[$i], $ids[$j]) . '-' . max($ids[$i], $ids[$j]);
                    $this->historialParejas[$key] = ($this->historialParejas[$key] ?? 0) + 1;
                }
            }
        }
    }

    private function actualizarHistorialParejas(array $ids): void
    {
        for ($i = 0; $i < count($ids); $i++) {
            for ($j = $i + 1; $j < count($ids); $j++) {
                $key = min($ids[$i], $ids[$j]) . '-' . max($ids[$i], $ids[$j]);
                $this->historialParejas[$key] = ($this->historialParejas[$key] ?? 0) + 1;
            }
        }
    }

    private function crearAsignacion(TurnoGenerado $turnoGenerado, Publicador $publicador, string $rol): void
    {
        TurnoAsignacion::create([
            'turno_id' => $turnoGenerado->turno_id,
            'turno_generado_id' => $turnoGenerado->id,
            'publicador_id' => $publicador->id,
            'fecha' => $turnoGenerado->fecha,
            'rol' => $rol,
            'estado' => 'pendiente',
        ]);

        $this->turnosAsignadosMes[$publicador->id] = ($this->turnosAsignadosMes[$publicador->id] ?? 0) + 1;
    }

    /**
     * Obtener alertas del mes con lista de publicadores
     */
    public function getAlertas(int $year, int $month): array
    {
        $alertasList = [];
        $publicadoresList = [];

        // Obtener turnos del mes
        $turnosTotales = TurnoGenerado::where('congregacion_id', $this->congregacionId)
            ->whereYear('fecha', $year)
            ->whereMonth('fecha', $month)
            ->count();

        // Obtener asignaciones del mes
        $asignaciones = TurnoAsignacion::whereHas('turnoGenerado', function($q) use ($year, $month) {
                $q->where('congregacion_id', $this->congregacionId)
                    ->whereYear('fecha', $year)
                    ->whereMonth('fecha', $month);
            })
            ->get();

        // Contar turnos completos
        $turnosCompletos = TurnoGenerado::where('congregacion_id', $this->congregacionId)
            ->whereYear('fecha', $year)
            ->whereMonth('fecha', $month)
            ->withCount('asignaciones')
            ->get()
            ->filter(fn($t) => $t->asignaciones_count >= 3)
            ->count();

        $publicadoresAsignadosCount = $asignaciones->pluck('publicador_id')->unique()->count();

        // Contar turnos por publicador
        $turnosPorPublicador = $asignaciones->groupBy('publicador_id')
            ->map(fn($grupo) => $grupo->count());

        // Obtener publicadores aprobados
        $publicadores = Publicador::where('congregacion_id', $this->congregacionId)
            ->where('aprobado_ppoc', true)
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        // Separar precursores y no precursores
        $precursores = $publicadores->filter(fn($p) => $p->es_precursor);
        $noPrecursores = $publicadores->filter(fn($p) => !$p->es_precursor);

        // Calcular medias
        $mediaPrecursores = $precursores->isEmpty() ? 0 :
            $precursores->map(fn($p) => $turnosPorPublicador[$p->id] ?? 0)->avg();

        $mediaPublicadores = $noPrecursores->isEmpty() ? 0 :
            $noPrecursores->map(fn($p) => $turnosPorPublicador[$p->id] ?? 0)->avg();

        // Evaluar cada publicador
        foreach ($publicadores as $pub) {
            $turnos = $turnosPorPublicador[$pub->id] ?? 0;
            $esPrecursor = $pub->es_precursor;
            $estado = 'ok';

            if ($esPrecursor) {
                // Precursor: danger si tiene menos que 70% de la media de publicadores
                if ($mediaPublicadores > 0 && $turnos < $mediaPublicadores * 0.7) {
                    $estado = 'danger';
                } elseif ($mediaPublicadores > 0 && $turnos < $mediaPublicadores) {
                    $estado = 'warning';
                }
            } else {
                // Publicador: danger si tiene mas que 130% de la media de precursores
                if ($mediaPrecursores > 0 && $turnos > $mediaPrecursores * 1.3) {
                    $estado = 'danger';
                } elseif ($mediaPrecursores > 0 && $turnos > $mediaPrecursores) {
                    $estado = 'warning';
                }
            }

            $publicadoresList[] = [
                'nombre' => $pub->nombre,
                'turnos' => $turnos,
                'es_precursor' => $esPrecursor,
                'estado' => $estado
            ];
        }

        // Ordenar: primero danger, luego warning, luego ok
        usort($publicadoresList, function($a, $b) {
            $orden = ['danger' => 0, 'warning' => 1, 'ok' => 2];
            return $orden[$a['estado']] <=> $orden[$b['estado']];
        });

        // Turnos incompletos
        $turnosIncompletos = TurnoGenerado::where('congregacion_id', $this->congregacionId)
            ->whereYear('fecha', $year)
            ->whereMonth('fecha', $month)
            ->withCount('asignaciones')
            ->get()
            ->filter(fn($t) => $t->asignaciones_count < 3)
            ->count();

        if ($turnosIncompletos > 0) {
            $alertasList[] = [
                'tipo' => 'danger',
                'mensaje' => "Hay {$turnosIncompletos} turnos incompletos"
            ];
        }

        // Calcular estadisticas detalladas para el panel
        $precursoresConTurnos = $precursores->map(function($p) use ($turnosPorPublicador) {
            return [
                'id' => $p->id,
                'nombre' => $p->nombre_completo,
                'turnos' => $turnosPorPublicador[$p->id] ?? 0,
                'es_capitan' => $p->es_capitan_ppoc,
            ];
        })->sortBy('turnos')->values();

        $publicadoresConTurnos = $noPrecursores->map(function($p) use ($turnosPorPublicador) {
            return [
                'id' => $p->id,
                'nombre' => $p->nombre_completo,
                'turnos' => $turnosPorPublicador[$p->id] ?? 0,
                'es_capitan' => $p->es_capitan_ppoc,
            ];
        })->sortBy('turnos')->values();

        // Extremos precursores
        $precursorMin = $precursoresConTurnos->first();
        $precursorMax = $precursoresConTurnos->last();

        // Extremos publicadores
        $publicadorMin = $publicadoresConTurnos->first();
        $publicadorMax = $publicadoresConTurnos->last();

        // Estadisticas de capitanes
        $capitanes = $publicadores->filter(fn($p) => $p->es_capitan_ppoc);
        $capitanesConTurnos = $capitanes->map(function($p) use ($turnosPorPublicador) {
            return [
                'id' => $p->id,
                'nombre' => $p->nombre_completo,
                'turnos' => $turnosPorPublicador[$p->id] ?? 0,
            ];
        })->sortBy('turnos')->values();

        $mediaCapitanes = $capitanes->isEmpty() ? 0 :
            $capitanes->map(fn($p) => $turnosPorPublicador[$p->id] ?? 0)->avg();

        $capitanMin = $capitanesConTurnos->first();
        $capitanMax = $capitanesConTurnos->last();

        return [
            'stats' => [
                'turnos_totales' => $turnosTotales,
                'turnos_completos' => $turnosCompletos,
                'asignaciones_totales' => $asignaciones->count(),
                'publicadores_asignados' => $publicadoresAsignadosCount,
            ],
            'publicadores' => $publicadoresList,
            'alertas' => $alertasList,
            'panel' => [
                'precursores' => [
                    'total' => $precursores->count(),
                    'media' => round($mediaPrecursores, 1),
                    'min' => $precursorMin,
                    'max' => $precursorMax,
                ],
                'publicadores' => [
                    'total' => $noPrecursores->count(),
                    'media' => round($mediaPublicadores, 1),
                    'min' => $publicadorMin,
                    'max' => $publicadorMax,
                ],
                'capitanes' => [
                    'total' => $capitanes->count(),
                    'media' => round($mediaCapitanes, 1),
                    'min' => $capitanMin,
                    'max' => $capitanMax,
                ],
            ],
        ];
    }
}
