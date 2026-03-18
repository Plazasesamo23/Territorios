<?php

namespace App\Services;

use App\Models\Publicador;
use App\Models\ReunionPrograma;
use App\Models\ReunionParte;
use App\Models\ReunionHistorial;
use Illuminate\Support\Collection;

class AsignacionReunionService
{
    private int $congregacionId;
    private Collection $publicadores;
    private array $historialPorTipo = [];
    private array $conteoGlobal = [];
    private array $asignadosEstaSemana = [];

    public function __construct(int $congregacionId)
    {
        $this->congregacionId = $congregacionId;
    }

    public function autoAsignar(ReunionPrograma $programa): array
    {
        $resultado = [
            'asignados' => 0,
            'sin_candidatos' => [],
            'errores' => [],
        ];

        $this->publicadores = Publicador::where('congregacion_id', $this->congregacionId)
            ->where('activo', true)
            ->whereNotNull('genero')
            ->get();

        if ($this->publicadores->isEmpty()) {
            $resultado['errores'][] = 'No hay publicadores activos con genero asignado.';
            return $resultado;
        }

        $sinGenero = Publicador::where('congregacion_id', $this->congregacionId)
            ->where('activo', true)
            ->whereNull('genero')
            ->count();

        if ($sinGenero > 0) {
            $resultado['errores'][] = "Hay {$sinGenero} publicadores sin genero asignado. Asignales genero antes de continuar.";
            return $resultado;
        }

        $this->cargarHistorial($programa->fecha_semana);
        $this->asignadosEstaSemana = [];

        // Orden de asignacion: mas restrictivo primero
        $orden = [
            'presidente',
            'oracion_inicio',
            'discurso_tesoros',
            'perlas',
            'discurso_vida',
            'conductor_estudio',
            'lectura',
            'lector_estudio',
            'oracion_final',
        ];

        // Roles globales del programa
        foreach ($orden as $tipo) {
            $campo = match ($tipo) {
                'presidente' => 'presidente_id',
                'oracion_inicio' => 'oracion_inicio_id',
                'oracion_final' => 'oracion_final_id',
                'conductor_estudio' => 'conductor_estudio_id',
                'lector_estudio' => 'lector_estudio_id',
                default => null,
            };

            if ($campo && !$programa->$campo) {
                $elegido = $this->elegirMejorCandidato($tipo, $programa->fecha_semana);
                if ($elegido) {
                    $programa->$campo = $elegido->id;
                    $this->registrarAsignacion($elegido->id, $tipo);
                    $resultado['asignados']++;
                } else {
                    $resultado['sin_candidatos'][] = $this->nombreTipo($tipo);
                }
            } elseif ($campo && $programa->$campo) {
                $this->registrarAsignacion($programa->$campo, $tipo);
            }
        }

        // Partes del programa (tesoros, maestros, vida_cristiana)
        $partesTesoros = ['discurso_tesoros', 'perlas', 'lectura'];
        $partesVida = ['discurso_vida'];

        foreach ($programa->partes as $parte) {
            // Asignar principal
            if (!$parte->publicador_id) {
                $elegido = $this->elegirMejorCandidato($parte->tipo, $programa->fecha_semana);
                if ($elegido) {
                    $parte->publicador_id = $elegido->id;
                    $this->registrarAsignacion($elegido->id, $parte->tipo);
                    $resultado['asignados']++;
                } else {
                    $resultado['sin_candidatos'][] = $parte->nombre_tipo . ' (#' . $parte->orden . ')';
                }
            } else {
                $this->registrarAsignacion($parte->publicador_id, $parte->tipo);
            }

            // Asignar ayudante
            if ($parte->necesita_ayudante && !$parte->ayudante_id) {
                $elegido = $this->elegirMejorCandidato('ayudante', $programa->fecha_semana);
                if ($elegido) {
                    $parte->ayudante_id = $elegido->id;
                    $this->registrarAsignacion($elegido->id, 'ayudante');
                    $resultado['asignados']++;
                } else {
                    $resultado['sin_candidatos'][] = 'Ayudante para ' . $parte->nombre_tipo;
                }
            }

            $parte->save();
        }

        $programa->save();

        // Guardar historial
        $this->guardarHistorial($programa);

        return $resultado;
    }

    private function elegirMejorCandidato(string $tipo, $fechaSemana): ?Publicador
    {
        $candidatos = $this->publicadores->filter(fn($p) => $p->puedeHacerParte($tipo));

        if ($candidatos->isEmpty()) {
            return null;
        }

        $fechaSemanaCarbon = \Carbon\Carbon::parse($fechaSemana);
        $puntuaciones = [];

        // Calcular media de asignaciones del grupo para equidad
        $conteos = $candidatos->map(fn($p) => $this->conteoGlobal[$p->id] ?? 0);
        $media = $conteos->avg() ?: 0;

        foreach ($candidatos as $candidato) {
            $puntuacion = 0.0;

            // +0.5 por cada dia desde su ultima asignacion de este TIPO (max +30)
            $ultimaFecha = $this->getUltimaAsignacionTipo($candidato->id, $tipo);
            if ($ultimaFecha) {
                $diasDesde = $ultimaFecha->diffInDays($fechaSemanaCarbon);
                $puntuacion += min($diasDesde * 0.5, 30);
            } else {
                $puntuacion += 30; // nunca asignado en este tipo
            }

            // Equidad: +8 por cada asignacion MENOS que la media, -10 por cada MAS
            $conteo = $this->conteoGlobal[$candidato->id] ?? 0;
            $diferencia = $media - $conteo;
            if ($diferencia > 0) {
                $puntuacion += $diferencia * 8;
            } else {
                $puntuacion += $diferencia * 10; // negativo
            }

            // -15 si ya tiene OTRA parte esta misma semana
            $asignacionesSemana = $this->asignadosEstaSemana[$candidato->id] ?? 0;
            if ($asignacionesSemana > 0) {
                $puntuacion -= 15 * $asignacionesSemana;
            }

            // -40 si fue asignado la semana pasada en el mismo TIPO
            $ultimaTipo = $this->getUltimaAsignacionTipo($candidato->id, $tipo);
            if ($ultimaTipo && $ultimaTipo->diffInDays($fechaSemanaCarbon) <= 7) {
                $puntuacion -= 40;
            }

            // Ancianos no suelen hacer partes de estudiantes
            $esParteEstudiante = in_array($tipo, ['empiece_conversaciones', 'haga_revisitas', 'haga_discipulos', 'explique_creencias', 'ayudante']);
            if ($esParteEstudiante && $candidato->es_anciano) {
                $puntuacion -= 50;
            }

            // Jitter aleatorio
            $puntuacion += rand(-200, 200) / 100;

            $puntuaciones[$candidato->id] = $puntuacion;
        }

        arsort($puntuaciones);
        $mejorId = array_key_first($puntuaciones);
        return $candidatos->firstWhere('id', $mejorId);
    }

    private function registrarAsignacion(int $publicadorId, string $tipo): void
    {
        $this->asignadosEstaSemana[$publicadorId] = ($this->asignadosEstaSemana[$publicadorId] ?? 0) + 1;
        $this->conteoGlobal[$publicadorId] = ($this->conteoGlobal[$publicadorId] ?? 0) + 1;
    }

    private function cargarHistorial($fechaSemana): void
    {
        $fechaDesde = \Carbon\Carbon::parse($fechaSemana)->subMonths(6);

        $historial = ReunionHistorial::where('congregacion_id', $this->congregacionId)
            ->where('fecha_semana', '>=', $fechaDesde)
            ->get();

        $this->historialPorTipo = [];
        $this->conteoGlobal = [];

        foreach ($historial as $h) {
            $key = $h->publicador_id . '-' . $h->tipo_parte;
            if (!isset($this->historialPorTipo[$key]) || $h->fecha_semana->gt($this->historialPorTipo[$key])) {
                $this->historialPorTipo[$key] = $h->fecha_semana;
            }
            $this->conteoGlobal[$h->publicador_id] = ($this->conteoGlobal[$h->publicador_id] ?? 0) + 1;
        }
    }

    private function getUltimaAsignacionTipo(int $publicadorId, string $tipo): ?\Carbon\Carbon
    {
        $key = $publicadorId . '-' . $tipo;
        return $this->historialPorTipo[$key] ?? null;
    }

    private function guardarHistorial(ReunionPrograma $programa): void
    {
        // Limpiar historial previo de este programa
        ReunionHistorial::where('programa_id', $programa->id)->delete();

        $roles = [
            'presidente' => $programa->presidente_id,
            'oracion_inicio' => $programa->oracion_inicio_id,
            'oracion_final' => $programa->oracion_final_id,
            'conductor_estudio' => $programa->conductor_estudio_id,
            'lector_estudio' => $programa->lector_estudio_id,
        ];

        foreach ($roles as $tipo => $pubId) {
            if ($pubId) {
                ReunionHistorial::create([
                    'congregacion_id' => $programa->congregacion_id,
                    'publicador_id' => $pubId,
                    'programa_id' => $programa->id,
                    'fecha_semana' => $programa->fecha_semana,
                    'tipo_parte' => $tipo,
                    'rol' => 'principal',
                ]);
            }
        }

        foreach ($programa->partes as $parte) {
            if ($parte->publicador_id) {
                ReunionHistorial::create([
                    'congregacion_id' => $programa->congregacion_id,
                    'publicador_id' => $parte->publicador_id,
                    'programa_id' => $programa->id,
                    'fecha_semana' => $programa->fecha_semana,
                    'tipo_parte' => $parte->tipo,
                    'rol' => 'principal',
                ]);
            }
            if ($parte->ayudante_id) {
                ReunionHistorial::create([
                    'congregacion_id' => $programa->congregacion_id,
                    'publicador_id' => $parte->ayudante_id,
                    'programa_id' => $programa->id,
                    'fecha_semana' => $programa->fecha_semana,
                    'tipo_parte' => $parte->tipo,
                    'rol' => 'ayudante',
                ]);
            }
        }
    }

    private function nombreTipo(string $tipo): string
    {
        return match ($tipo) {
            'presidente' => 'Presidente',
            'oracion_inicio' => 'Oracion de inicio',
            'oracion_final' => 'Oracion final',
            'conductor_estudio' => 'Conductor del estudio',
            'lector_estudio' => 'Lector del estudio',
            'discurso_tesoros' => 'Discurso Tesoros',
            'perlas' => 'Perlas escondidas',
            'lectura' => 'Lectura biblica',
            'discurso_vida' => 'Discurso Vida Cristiana',
            default => $tipo,
        };
    }

    /**
     * Genera las partes estandar para un programa nuevo
     */
    public static function generarPartesEstandar(ReunionPrograma $programa): void
    {
        $partes = [
            // Tesoros de la Biblia
            ['seccion' => 'tesoros', 'tipo' => 'discurso_tesoros', 'titulo' => null, 'duracion_minutos' => 10, 'orden' => 1, 'necesita_ayudante' => false],
            ['seccion' => 'tesoros', 'tipo' => 'perlas', 'titulo' => 'Busquemos perlas escondidas', 'duracion_minutos' => 10, 'orden' => 2, 'necesita_ayudante' => false],
            ['seccion' => 'tesoros', 'tipo' => 'lectura', 'titulo' => 'Lectura de la Biblia', 'duracion_minutos' => 4, 'orden' => 3, 'necesita_ayudante' => false],

            // Seamos mejores maestros
            ['seccion' => 'maestros', 'tipo' => 'empiece_conversaciones', 'titulo' => 'Empiece conversaciones', 'duracion_minutos' => 3, 'orden' => 4, 'necesita_ayudante' => true],
            ['seccion' => 'maestros', 'tipo' => 'haga_revisitas', 'titulo' => 'Haga revisitas', 'duracion_minutos' => 4, 'orden' => 5, 'necesita_ayudante' => true],
            ['seccion' => 'maestros', 'tipo' => 'haga_discipulos', 'titulo' => 'Haga discipulos', 'duracion_minutos' => 5, 'orden' => 6, 'necesita_ayudante' => true],

            // Nuestra vida cristiana
            ['seccion' => 'vida_cristiana', 'tipo' => 'discurso_vida', 'titulo' => null, 'duracion_minutos' => 15, 'orden' => 7, 'necesita_ayudante' => false],
        ];

        foreach ($partes as $parte) {
            ReunionParte::create(array_merge($parte, ['programa_id' => $programa->id]));
        }
    }
}
