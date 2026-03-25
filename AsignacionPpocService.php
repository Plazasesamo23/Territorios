<?php

namespace App\Services;

use App\Models\Turno;
use App\Models\TurnoGenerado;
use App\Models\TurnoAsignacion;
use App\Models\Publicador;
use App\Models\DisponibilidadPpoc;
use App\Models\RelacionFamiliar;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AsignacionPpocService
{
    private int $congregacionId;
    private array $historialParejasCapitanVoluntario = [];
    private array $turnosAsignadosMes = [];
    private array $conyuges = [];
    private array $turnosConConyuge = [];

    public function __construct(int $congregacionId)
    {
        $this->congregacionId = $congregacionId;
    }

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

        $this->cargarHistorialParejas($year, $month);
        $this->cargarConyuges();
        $this->turnosAsignadosMes = [];
        $this->turnosConConyuge = [];

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

        $capitanSeleccionado = $this->seleccionarCapitan($capitanes, $turnoGenerado, $voluntarios);

        if (!$capitanSeleccionado) {
            return $resultado;
        }

        $resultado['tiene_capitan'] = true;
        $asignados = [$capitanSeleccionado->id];

        $this->crearAsignacion($turnoGenerado, $capitanSeleccionado, 'capitan');

        for ($i = 0; $i < 2; $i++) {
            $voluntarioSeleccionado = $this->seleccionarVoluntario(
                $voluntarios,
                $asignados,
                $turnoGenerado,
                $capitanSeleccionado
            );

            if ($voluntarioSeleccionado) {
                $asignados[] = $voluntarioSeleccionado->id;
                $this->crearAsignacion($turnoGenerado, $voluntarioSeleccionado, 'voluntario');
                $resultado['voluntarios']++;
                $voluntarios = $voluntarios->reject(fn($p) => $p->id === $voluntarioSeleccionado->id);

                $this->registrarSiEsConyuge($capitanSeleccionado->id, $voluntarioSeleccionado->id);
            }
        }

        $this->actualizarHistorialParejas($capitanSeleccionado->id, array_slice($asignados, 1));

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

    private function seleccionarCapitan(Collection $capitanes, TurnoGenerado $turno, Collection $voluntariosDisponibles): ?Publicador
    {
        if ($capitanes->isEmpty()) {
            return null;
        }

        $puntuaciones = [];
        foreach ($capitanes as $capitan) {
            $puntuacion = 100.0;

            $turnosMes = $this->turnosAsignadosMes[$capitan->id] ?? 0;
            $puntuacion -= ($turnosMes * 20);

            if ($capitan->es_precursor) {
                $puntuacion += 25;
            }

            $voluntariosSinRepetir = 0;
            foreach ($voluntariosDisponibles as $vol) {
                if (!$this->excedeLimitePareja($capitan->id, $vol->id)) {
                    $voluntariosSinRepetir++;
                }
            }
            $puntuacion += ($voluntariosSinRepetir * 5);

            $conyugeId = $this->getConyugeId($capitan->id);
            if ($conyugeId && $voluntariosDisponibles->contains('id', $conyugeId)) {
                if ($this->debeSalirConConyuge($capitan->id)) {
                    $puntuacion += 15;
                }
            }

            $puntuacion += rand(-3, 3);

            $puntuaciones[$capitan->id] = $puntuacion;
        }

        arsort($puntuaciones);
        $mejorId = array_key_first($puntuaciones);
        return $capitanes->firstWhere('id', $mejorId);
    }

    private function seleccionarVoluntario(Collection $voluntarios, array $yaAsignados, TurnoGenerado $turno, Publicador $capitan): ?Publicador
    {
        $voluntarios = $voluntarios->reject(fn($p) => in_array($p->id, $yaAsignados));

        if ($voluntarios->isEmpty()) {
            return null;
        }

        $conyugeCapitan = $this->getConyugeId($capitan->id);
        $debePriorizarConyuge = $conyugeCapitan &&
                                 $voluntarios->contains('id', $conyugeCapitan) &&
                                 $this->debeSalirConConyuge($capitan->id) &&
                                 !in_array($conyugeCapitan, $yaAsignados);

        $puntuaciones = [];
        foreach ($voluntarios as $voluntario) {
            $puntuacion = 100.0;

            // REGLA 1: Evitar repetir pareja capitan-voluntario (max 2 veces/mes)
            $vecesJuntos = $this->vecesJuntosCapitanVoluntario($capitan->id, $voluntario->id);
            if ($vecesJuntos >= 2) {
                $puntuacion -= 50;
            } elseif ($vecesJuntos == 1) {
                $puntuacion -= 20;
            }

            // REGLA 2: Conyuges juntos (~50% del tiempo)
            if ($voluntario->id === $conyugeCapitan && $debePriorizarConyuge) {
                $puntuacion += 40;
            }

            $turnosMes = $this->turnosAsignadosMes[$voluntario->id] ?? 0;
            $puntuacion -= ($turnosMes * 15);

            if ($voluntario->es_precursor) {
                $puntuacion += 20;
            }

            $puntuacion += rand(-3, 3);

            $puntuaciones[$voluntario->id] = $puntuacion;
        }

        arsort($puntuaciones);
        $mejorId = array_key_first($puntuaciones);
        return $voluntarios->firstWhere('id', $mejorId);
    }

    private function cargarHistorialParejas(int $year, int $month): void
    {
        // Cargar historial del mes actual + 3 meses anteriores
        $fechaInicio = Carbon::create($year, $month, 1)->subMonths(3);
        $fechaFin = Carbon::create($year, $month, 1)->endOfMonth();

        $asignaciones = TurnoAsignacion::whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->whereHas('turnoGenerado', fn($q) => $q->where('congregacion_id', $this->congregacionId))
            ->get()
            ->groupBy('turno_generado_id');

        $this->historialParejasCapitanVoluntario = [];

        foreach ($asignaciones as $turnoId => $grupo) {
            $capitan = $grupo->firstWhere('rol', 'capitan');
            $voluntarios = $grupo->where('rol', 'voluntario');

            if ($capitan) {
                foreach ($voluntarios as $vol) {
                    $key = $capitan->publicador_id . '-' . $vol->publicador_id;
                    $this->historialParejasCapitanVoluntario[$key] =
                        ($this->historialParejasCapitanVoluntario[$key] ?? 0) + 1;
                }
            }
        }
    }

    private function cargarConyuges(): void
    {
        $relaciones = RelacionFamiliar::where('tipo_relacion', 'conyuge')->get();

        $this->conyuges = [];
        foreach ($relaciones as $rel) {
            $this->conyuges[$rel->publicador_id] = $rel->familiar_id;
            $this->conyuges[$rel->familiar_id] = $rel->publicador_id;
        }
    }

    private function getConyugeId(int $publicadorId): ?int
    {
        return $this->conyuges[$publicadorId] ?? null;
    }

    private function debeSalirConConyuge(int $publicadorId): bool
    {
        $turnosTotales = $this->turnosAsignadosMes[$publicadorId] ?? 0;
        $turnosConConyuge = $this->turnosConConyuge[$publicadorId] ?? 0;

        if ($turnosTotales == 0) {
            return rand(0, 1) == 1;
        }

        $porcentaje = $turnosConConyuge / max($turnosTotales, 1);
        return $porcentaje < 0.5;
    }

    private function registrarSiEsConyuge(int $id1, int $id2): void
    {
        if ($this->getConyugeId($id1) === $id2) {
            $this->turnosConConyuge[$id1] = ($this->turnosConConyuge[$id1] ?? 0) + 1;
            $this->turnosConConyuge[$id2] = ($this->turnosConConyuge[$id2] ?? 0) + 1;
        }
    }

    private function vecesJuntosCapitanVoluntario(int $capitanId, int $voluntarioId): int
    {
        $key = $capitanId . '-' . $voluntarioId;
        return $this->historialParejasCapitanVoluntario[$key] ?? 0;
    }

    private function excedeLimitePareja(int $capitanId, int $voluntarioId): bool
    {
        return $this->vecesJuntosCapitanVoluntario($capitanId, $voluntarioId) >= 2;
    }

    private function actualizarHistorialParejas(int $capitanId, array $voluntarioIds): void
    {
        foreach ($voluntarioIds as $volId) {
            $key = $capitanId . '-' . $volId;
            $this->historialParejasCapitanVoluntario[$key] =
                ($this->historialParejasCapitanVoluntario[$key] ?? 0) + 1;
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

    public function getAlertas(int $year, int $month): array
    {
        $alertasList = [];
        $publicadoresList = [];

        $turnosTotales = TurnoGenerado::where('congregacion_id', $this->congregacionId)
            ->whereYear('fecha', $year)
            ->whereMonth('fecha', $month)
            ->count();

        $asignaciones = TurnoAsignacion::whereHas('turnoGenerado', function($q) use ($year, $month) {
                $q->where('congregacion_id', $this->congregacionId)
                    ->whereYear('fecha', $year)
                    ->whereMonth('fecha', $month);
            })
            ->get();

        $turnosCompletos = TurnoGenerado::where('congregacion_id', $this->congregacionId)
            ->whereYear('fecha', $year)
            ->whereMonth('fecha', $month)
            ->withCount('asignaciones')
            ->get()
            ->filter(fn($t) => $t->asignaciones_count >= 3)
            ->count();

        $publicadoresAsignadosCount = $asignaciones->pluck('publicador_id')->unique()->count();

        $turnosPorPublicador = $asignaciones->groupBy('publicador_id')
            ->map(fn($grupo) => $grupo->count());

        $publicadores = Publicador::where('congregacion_id', $this->congregacionId)
            ->where('aprobado_ppoc', true)
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        $precursores = $publicadores->filter(fn($p) => $p->es_precursor);
        $noPrecursores = $publicadores->filter(fn($p) => !$p->es_precursor);

        $mediaPrecursores = $precursores->isEmpty() ? 0 :
            $precursores->map(fn($p) => $turnosPorPublicador[$p->id] ?? 0)->avg();

        $mediaPublicadores = $noPrecursores->isEmpty() ? 0 :
            $noPrecursores->map(fn($p) => $turnosPorPublicador[$p->id] ?? 0)->avg();

        foreach ($publicadores as $pub) {
            $turnos = $turnosPorPublicador[$pub->id] ?? 0;
            $esPrecursor = $pub->es_precursor;
            $estado = 'ok';

            if ($esPrecursor) {
                if ($mediaPublicadores > 0 && $turnos < $mediaPublicadores * 0.7) {
                    $estado = 'danger';
                } elseif ($mediaPublicadores > 0 && $turnos < $mediaPublicadores) {
                    $estado = 'warning';
                }
            } else {
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

        usort($publicadoresList, function($a, $b) {
            $orden = ['danger' => 0, 'warning' => 1, 'ok' => 2];
            return $orden[$a['estado']] <=> $orden[$b['estado']];
        });

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

        $precursorMin = $precursoresConTurnos->first();
        $precursorMax = $precursoresConTurnos->last();
        $publicadorMin = $publicadoresConTurnos->first();
        $publicadorMax = $publicadoresConTurnos->last();

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
