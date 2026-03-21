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
    private array $conteoPorTipo = [];
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
            ->where('excluido_reuniones', false)
            ->get();

        if ($this->publicadores->isEmpty()) {
            $resultado['errores'][] = 'No hay publicadores activos con genero asignado.';
            return $resultado;
        }

        $sinGenero = Publicador::where('congregacion_id', $this->congregacionId)
            ->where('activo', true)
            ->whereNull('genero')
            ->count();

        $this->cargarHistorial($programa->fecha_semana);
        $this->asignadosEstaSemana = [];

        // Recargar partes frescas de la BD
        $programa->load('partes');

        // Limpiar asignaciones invalidas (publicador sin autorizacion o inexistente)
        $this->limpiarAsignacionesInvalidas($programa);

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

            // Asignar ayudante (mismo genero o conyuge en partes de maestros)
            if ($parte->necesita_ayudante && !$parte->ayudante_id) {
                $estudiante = $parte->publicador_id
                    ? $this->publicadores->firstWhere('id', $parte->publicador_id)
                    : null;
                $elegido = $this->elegirMejorAyudante($programa->fecha_semana, $estudiante);
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

        // Media POR TIPO entre los candidatos elegibles (equidad por tipo)
        $conteosTipo = $candidatos->map(fn($p) => $this->conteoPorTipo[$p->id . '-' . $tipo] ?? 0);
        $mediaTipo = $conteosTipo->avg() ?: 0;

        // Media GLOBAL entre los candidatos (equidad global)
        $conteosGlobal = $candidatos->map(fn($p) => $this->conteoGlobal[$p->id] ?? 0);
        $mediaGlobal = $conteosGlobal->avg() ?: 0;

        // Cuantos candidatos hay para este tipo (para calibrar rotacion)
        $numCandidatos = $candidatos->count();

        foreach ($candidatos as $candidato) {
            $puntuacion = 0.0;

            // --- ROTACION POR TIPO: dias desde ultima asignacion de este TIPO ---
            $ultimaFecha = $this->getUltimaAsignacionTipo($candidato->id, $tipo);
            if ($ultimaFecha) {
                $diasDesde = $ultimaFecha->diffInDays($fechaSemanaCarbon);
                // Escalar segun numero de candidatos: con mas candidatos, esperar mas dias
                $diasIdeal = $numCandidatos * 7; // rotar cada N semanas segun candidatos
                $puntuacion += min($diasDesde / max($diasIdeal, 1) * 25, 30);
            } else {
                $puntuacion += 35; // nunca asignado en este tipo - prioridad alta
            }

            // --- EQUIDAD POR TIPO: cuantas veces hizo ESTE tipo vs la media del tipo ---
            $conteoTipo = $this->conteoPorTipo[$candidato->id . '-' . $tipo] ?? 0;
            $difTipo = $mediaTipo - $conteoTipo;
            $puntuacion += $difTipo * 12; // peso fuerte: +12 por cada asignacion menos que la media

            // --- EQUIDAD GLOBAL: total asignaciones vs media global ---
            $conteoTotal = $this->conteoGlobal[$candidato->id] ?? 0;
            $difGlobal = $mediaGlobal - $conteoTotal;
            $puntuacion += $difGlobal * 5; // peso moderado: evitar sobrecarga total

            // --- PENALIZACION: ya tiene parte esta misma semana ---
            $asignacionesSemana = $this->asignadosEstaSemana[$candidato->id] ?? 0;
            if ($asignacionesSemana > 0) {
                $puntuacion -= 20 * $asignacionesSemana;
            }

            // --- PENALIZACION: mismo tipo la semana pasada ---
            if ($ultimaFecha && $ultimaFecha->diffInDays($fechaSemanaCarbon) <= 7) {
                $puntuacion -= 50;
            }

            // --- PENALIZACION: mismo tipo hace 2 semanas ---
            if ($ultimaFecha && $ultimaFecha->diffInDays($fechaSemanaCarbon) <= 14) {
                $puntuacion -= 15;
            }

            // --- CONTEXTO: ancianos no hacen partes de estudiantes ---
            $esParteEstudiante = in_array($tipo, ['empiece_conversaciones', 'haga_revisitas', 'haga_discipulos', 'explique_creencias', 'ayudante']);
            if ($esParteEstudiante && $candidato->es_anciano) {
                $puntuacion -= 60;
            }

            // --- CONTEXTO: siervos ministeriales menos frecuentes en partes de estudiantes ---
            if ($esParteEstudiante && $candidato->es_siervo_ministerial) {
                $puntuacion -= 20;
            }

            // Jitter minimo (solo para desempate, no para alterar la equidad)
            $puntuacion += rand(-50, 50) / 100; // +-0.5 puntos

            $puntuaciones[$candidato->id] = $puntuacion;
        }

        arsort($puntuaciones);
        $mejorId = array_key_first($puntuaciones);
        return $candidatos->firstWhere('id', $mejorId);
    }

    /**
     * Elegir ayudante respetando regla de genero:
     * - Mismo genero que el estudiante
     * - Excepcion: conyuge (matrimonio) puede ser de genero opuesto
     */
    private function elegirMejorAyudante($fechaSemana, ?Publicador $estudiante): ?Publicador
    {
        $candidatos = $this->publicadores->filter(fn($p) => $p->puedeHacerParte('ayudante'));

        if ($candidatos->isEmpty()) {
            return null;
        }

        // Si hay estudiante asignado, filtrar por genero (excepto conyuge)
        if ($estudiante) {
            $conyugeId = $estudiante->getConyugeId();
            $generoEstudiante = $estudiante->genero;

            $candidatos = $candidatos->filter(function ($p) use ($estudiante, $generoEstudiante, $conyugeId) {
                // No puede ser el mismo estudiante
                if ($p->id === $estudiante->id) return false;
                // Mismo genero siempre OK
                if ($p->genero === $generoEstudiante) return true;
                // Genero opuesto solo si es conyuge
                if ($conyugeId && $p->id === $conyugeId) return true;
                return false;
            });

            if ($candidatos->isEmpty()) {
                return null;
            }
        }

        // Usar el mismo scoring que elegirMejorCandidato pero para tipo 'ayudante'
        $fechaSemanaCarbon = \Carbon\Carbon::parse($fechaSemana);
        $puntuaciones = [];

        $conteosTipo = $candidatos->map(fn($p) => $this->conteoPorTipo[$p->id . '-ayudante'] ?? 0);
        $mediaTipo = $conteosTipo->avg() ?: 0;
        $conteosGlobal = $candidatos->map(fn($p) => $this->conteoGlobal[$p->id] ?? 0);
        $mediaGlobal = $conteosGlobal->avg() ?: 0;
        $numCandidatos = $candidatos->count();

        foreach ($candidatos as $candidato) {
            $puntuacion = 0.0;

            $ultimaFecha = $this->getUltimaAsignacionTipo($candidato->id, 'ayudante');
            if ($ultimaFecha) {
                $diasDesde = $ultimaFecha->diffInDays($fechaSemanaCarbon);
                $diasIdeal = $numCandidatos * 7;
                $puntuacion += min($diasDesde / max($diasIdeal, 1) * 25, 30);
            } else {
                $puntuacion += 35;
            }

            $conteoTipo = $this->conteoPorTipo[$candidato->id . '-ayudante'] ?? 0;
            $puntuacion += ($mediaTipo - $conteoTipo) * 12;

            $conteoTotal = $this->conteoGlobal[$candidato->id] ?? 0;
            $puntuacion += ($mediaGlobal - $conteoTotal) * 5;

            $asignacionesSemana = $this->asignadosEstaSemana[$candidato->id] ?? 0;
            if ($asignacionesSemana > 0) {
                $puntuacion -= 20 * $asignacionesSemana;
            }

            if ($ultimaFecha && $ultimaFecha->diffInDays($fechaSemanaCarbon) <= 7) {
                $puntuacion -= 50;
            }

            // Conyuge del estudiante tiene bonus leve (natural hacerlo juntos)
            if ($estudiante && $estudiante->getConyugeId() === $candidato->id) {
                $puntuacion += 5;
            }

            $puntuacion += rand(-50, 50) / 100;
            $puntuaciones[$candidato->id] = $puntuacion;
        }

        arsort($puntuaciones);
        $mejorId = array_key_first($puntuaciones);
        return $candidatos->firstWhere('id', $mejorId);
    }

    /**
     * Limpia asignaciones donde el publicador ya no existe, esta inactivo,
     * fue excluido, o perdio la autorizacion para ese tipo de parte.
     */
    private function limpiarAsignacionesInvalidas(ReunionPrograma $programa): void
    {
        $pubIds = $this->publicadores->pluck('id')->toArray();

        // Roles globales
        $rolesGlobales = [
            'presidente_id' => 'presidente',
            'oracion_inicio_id' => 'oracion_inicio',
            'oracion_final_id' => 'oracion_final',
            'conductor_estudio_id' => 'conductor_estudio',
            'lector_estudio_id' => 'lector_estudio',
        ];

        foreach ($rolesGlobales as $campo => $tipo) {
            if ($programa->$campo) {
                $pub = $this->publicadores->firstWhere('id', $programa->$campo);
                if (!$pub || !$pub->puedeHacerParte($tipo)) {
                    $programa->$campo = null;
                }
            }
        }

        // Partes individuales
        foreach ($programa->partes as $parte) {
            if ($parte->publicador_id) {
                $pub = $this->publicadores->firstWhere('id', $parte->publicador_id);
                if (!$pub || !$pub->puedeHacerParte($parte->tipo)) {
                    $parte->publicador_id = null;
                }
            }
            if ($parte->ayudante_id) {
                $ayu = $this->publicadores->firstWhere('id', $parte->ayudante_id);
                $invalido = false;

                if (!$ayu || !$ayu->puedeHacerParte('ayudante')) {
                    $invalido = true;
                }

                // Verificar compatibilidad de genero con el estudiante
                if (!$invalido && $parte->publicador_id) {
                    $est = $this->publicadores->firstWhere('id', $parte->publicador_id);
                    if ($est && $ayu->genero !== $est->genero) {
                        // Genero opuesto: solo valido si son conyuges
                        $conyugeId = $est->getConyugeId();
                        if (!$conyugeId || $conyugeId !== $ayu->id) {
                            $invalido = true;
                        }
                    }
                }

                if ($invalido) {
                    $parte->ayudante_id = null;
                }
            }
            $parte->save();
        }

        $programa->save();
    }

    private function registrarAsignacion(int $publicadorId, string $tipo): void
    {
        $this->asignadosEstaSemana[$publicadorId] = ($this->asignadosEstaSemana[$publicadorId] ?? 0) + 1;
        $this->conteoGlobal[$publicadorId] = ($this->conteoGlobal[$publicadorId] ?? 0) + 1;
        $key = $publicadorId . '-' . $tipo;
        $this->conteoPorTipo[$key] = ($this->conteoPorTipo[$key] ?? 0) + 1;
    }

    private function cargarHistorial($fechaSemana): void
    {
        // Cargar 12 meses para mejor equidad a largo plazo
        $fechaDesde = \Carbon\Carbon::parse($fechaSemana)->subMonths(12);

        $historial = ReunionHistorial::where('congregacion_id', $this->congregacionId)
            ->where('fecha_semana', '>=', $fechaDesde)
            ->get();

        $this->historialPorTipo = [];
        $this->conteoGlobal = [];
        $this->conteoPorTipo = [];

        foreach ($historial as $h) {
            // Ultima fecha por tipo
            $key = $h->publicador_id . '-' . $h->tipo_parte;
            if (!isset($this->historialPorTipo[$key]) || $h->fecha_semana->gt($this->historialPorTipo[$key])) {
                $this->historialPorTipo[$key] = $h->fecha_semana;
            }
            // Conteo global
            $this->conteoGlobal[$h->publicador_id] = ($this->conteoGlobal[$h->publicador_id] ?? 0) + 1;
            // Conteo por tipo
            $this->conteoPorTipo[$key] = ($this->conteoPorTipo[$key] ?? 0) + 1;
        }
    }

    private function getUltimaAsignacionTipo(int $publicadorId, string $tipo): ?\Carbon\Carbon
    {
        $key = $publicadorId . '-' . $tipo;
        return $this->historialPorTipo[$key] ?? null;
    }

    public function guardarHistorial(ReunionPrograma $programa): void
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
            'discurso_maestros' => 'Discurso Maestros',
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
