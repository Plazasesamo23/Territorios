<?php

namespace App\Http\Controllers\Reuniones;

use App\Http\Controllers\Controller;
use App\Models\ReunionPrograma;
use App\Models\ReunionParte;
use App\Models\ReunionAutorizacion;
use App\Models\Publicador;
use App\Services\AsignacionReunionService;
use App\Services\ImportadorVymService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReunionController extends Controller
{
    public function index()
    {
        $congregacionId = session('congregacion_activa_id');

        // Auto-generar las proximas 4 semanas si no existen
        $this->autoGenerarSemanas($congregacionId, 4);

        // Semana actual primero, futuras despues, pasadas al final
        $lunesActual = Carbon::now()->startOfWeek(Carbon::MONDAY);

        $programas = ReunionPrograma::where('congregacion_id', $congregacionId)
            ->orderByRaw("CASE WHEN fecha_semana = ? THEN 0 WHEN fecha_semana > ? THEN 1 ELSE 2 END", [$lunesActual, $lunesActual])
            ->orderBy('fecha_semana')
            ->paginate(20);

        return view('reuniones.index', compact('programas'));
    }

    private function autoGenerarSemanas(int $congregacionId, int $semanas): void
    {
        $lunes = Carbon::now()->startOfWeek(Carbon::MONDAY);

        for ($i = 0; $i < $semanas; $i++) {
            $fecha = $lunes->copy()->addWeeks($i);

            $existe = ReunionPrograma::where('congregacion_id', $congregacionId)
                ->where('fecha_semana', $fecha)
                ->exists();

            if (!$existe) {
                $programa = ReunionPrograma::create([
                    'congregacion_id' => $congregacionId,
                    'fecha_semana' => $fecha,
                    'estado' => 'borrador',
                ]);

                // Partes estandar como placeholder - el usuario importa de jw.org desde el editor
                AsignacionReunionService::generarPartesEstandar($programa);
            }
        }
    }

    public function create()
    {
        // Calcular proximo lunes disponible
        $congregacionId = session('congregacion_activa_id');
        $ultimoPrograma = ReunionPrograma::where('congregacion_id', $congregacionId)
            ->orderByDesc('fecha_semana')
            ->first();

        if ($ultimoPrograma) {
            $proximoLunes = $ultimoPrograma->fecha_semana->copy()->addWeek();
        } else {
            $proximoLunes = Carbon::now()->startOfWeek(Carbon::MONDAY);
            if ($proximoLunes->isPast()) {
                $proximoLunes->addWeek();
            }
        }

        return view('reuniones.create', compact('proximoLunes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'fecha_semana' => 'required|date',
            'cantidad_semanas' => 'required|integer|min:1|max:52',
        ]);

        $congregacionId = session('congregacion_activa_id');
        $fechaInicio = Carbon::parse($request->fecha_semana)->startOfWeek(Carbon::MONDAY);
        $cantidad = $request->cantidad_semanas;
        $creados = 0;
        $importados = 0;
        $importador = new ImportadorVymService();

        for ($i = 0; $i < $cantidad; $i++) {
            $fecha = $fechaInicio->copy()->addWeeks($i);

            $existe = ReunionPrograma::where('congregacion_id', $congregacionId)
                ->where('fecha_semana', $fecha)
                ->exists();

            if (!$existe) {
                $programa = ReunionPrograma::create([
                    'congregacion_id' => $congregacionId,
                    'fecha_semana' => $fecha,
                    'estado' => 'borrador',
                ]);

                // Intentar importar titulos de jw.org automaticamente
                $resultado = $importador->importar($programa);
                if (!empty($resultado['success'])) {
                    $importados++;
                } else {
                    // Fallback: partes estandar sin titulos
                    AsignacionReunionService::generarPartesEstandar($programa);
                }
                $creados++;
            }
        }

        if ($creados === 0) {
            return redirect()->route('reuniones.index')->with('error', 'Los programas para esas semanas ya existen.');
        }

        $msg = "Se crearon {$creados} programas.";
        if ($importados > 0) {
            $msg .= " {$importados} con titulos importados de jw.org.";
        }

        return redirect()->route('reuniones.index')->with('success', $msg);
    }

    public function edit(ReunionPrograma $reunione)
    {
        $programa = $reunione;
        $congregacionId = session('congregacion_activa_id');

        if ($programa->congregacion_id !== (int)$congregacionId) {
            abort(403);
        }

        $programa->load('partes', 'presidente', 'oracionInicio', 'oracionFinal', 'conductorEstudio', 'lectorEstudio');

        $publicadores = Publicador::where('congregacion_id', $congregacionId)
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        $sinGenero = $publicadores->whereNull('genero')->count();

        // Cargar autorizaciones para los dropdowns
        $autorizacionesDB = ReunionAutorizacion::where('congregacion_id', $congregacionId)->get();
        $autPorTipo = [];
        foreach ($autorizacionesDB->groupBy('tipo_parte') as $tipo => $items) {
            $autPorTipo[$tipo] = $items->pluck('publicador_id')->toArray();
        }

        // Mapa de conyuges para filtro de ayudantes
        $conyuges = \Illuminate\Support\Facades\DB::table('relaciones_familiares')
            ->where('tipo_relacion', 'conyuge')
            ->pluck('familiar_id', 'publicador_id')
            ->toArray();

        return view('reuniones.edit', compact('programa', 'publicadores', 'sinGenero', 'autPorTipo', 'conyuges'));
    }

    public function update(Request $request, ReunionPrograma $reunione)
    {
        $programa = $reunione;
        $congregacionId = session('congregacion_activa_id');

        if ($programa->congregacion_id !== (int)$congregacionId) {
            abort(403);
        }

        // Actualizar roles globales
        $programa->update([
            'presidente_id' => $request->presidente_id ?: null,
            'oracion_inicio_id' => $request->oracion_inicio_id ?: null,
            'oracion_final_id' => $request->oracion_final_id ?: null,
            'conductor_estudio_id' => $request->conductor_estudio_id ?: null,
            'lector_estudio_id' => $request->lector_estudio_id ?: null,
            'notas' => $request->notas,
        ]);

        // Actualizar partes
        if ($request->has('partes')) {
            foreach ($request->partes as $parteId => $datos) {
                $parte = ReunionParte::find($parteId);
                if ($parte && $parte->programa_id === $programa->id) {
                    $parte->update([
                        'publicador_id' => $datos['publicador_id'] ?: null,
                        'ayudante_id' => $datos['ayudante_id'] ?: null,
                        'titulo' => $datos['titulo'] ?? $parte->titulo,
                        'duracion_minutos' => $datos['duracion_minutos'] ?? $parte->duracion_minutos,
                    ]);
                }
            }
        }

        // Regenerar historial con las asignaciones manuales
        $service = new AsignacionReunionService($congregacionId);
        $programa->load('partes');
        $service->guardarHistorial($programa);

        return redirect()->route('reuniones.edit', $programa)->with('success', 'Programa actualizado correctamente.');
    }

    public function show(ReunionPrograma $reunione)
    {
        $programa = $reunione;
        $congregacionId = session('congregacion_activa_id');

        if ($programa->congregacion_id !== (int)$congregacionId) {
            abort(403);
        }

        $programa->load('partes.publicador', 'partes.ayudante', 'presidente', 'oracionInicio', 'oracionFinal', 'conductorEstudio', 'lectorEstudio');

        return view('reuniones.show', compact('programa'));
    }

    public function autoAsignar(ReunionPrograma $reunione)
    {
        $programa = $reunione;
        $congregacionId = session('congregacion_activa_id');

        if ($programa->congregacion_id !== (int)$congregacionId) {
            abort(403);
        }

        $service = new AsignacionReunionService($congregacionId);
        $resultado = $service->autoAsignar($programa);

        if (!empty($resultado['errores'])) {
            return redirect()->route('reuniones.edit', $programa)
                ->with('error', implode(' ', $resultado['errores']));
        }

        if ($resultado['asignados'] === 0 && empty($resultado['sin_candidatos'])) {
            $msg = 'Todas las partes ya estan asignadas.';
        } else {
            $msg = "Auto-asignacion: {$resultado['asignados']} asignaciones realizadas.";
        }

        if (!empty($resultado['sin_candidatos'])) {
            return redirect()->route('reuniones.edit', $programa)
                ->with('success', $msg)
                ->with('error', 'Sin candidatos autorizados para: ' . implode(', ', $resultado['sin_candidatos']) . '. Revisa las autorizaciones.');
        }

        $sinGenero = Publicador::where('congregacion_id', $congregacionId)
            ->where('activo', true)
            ->whereNull('genero')
            ->count();
        if ($sinGenero > 0) {
            $msg .= " (Aviso: {$sinGenero} publicadores sin genero asignado)";
        }

        return redirect()->route('reuniones.edit', $programa)->with('success', $msg);
    }

    public function importarTitulos(Request $request, ReunionPrograma $reunione)
    {
        $programa = $reunione;
        $congregacionId = session('congregacion_activa_id');

        if ($programa->congregacion_id !== (int)$congregacionId) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $request->validate(['partes' => 'required|array']);

        // Eliminar partes existentes y recrear con titulos importados
        $programa->partes()->delete();

        $orden = 1;
        foreach ($request->partes as $parte) {
            ReunionParte::create([
                'programa_id' => $programa->id,
                'seccion' => $parte['seccion'],
                'tipo' => $parte['tipo'],
                'titulo' => $parte['titulo'],
                'duracion_minutos' => $parte['duracion'],
                'orden' => $orden++,
                'necesita_ayudante' => $parte['necesita_ayudante'] ?? false,
            ]);
        }

        return response()->json(['success' => true, 'partes' => count($request->partes)]);
    }

    public function publicar(ReunionPrograma $reunione)
    {
        $programa = $reunione;
        $congregacionId = session('congregacion_activa_id');

        if ($programa->congregacion_id !== (int)$congregacionId) {
            abort(403);
        }

        $programa->update(['estado' => 'publicado']);

        // Guardar historial al publicar
        $service = new AsignacionReunionService($congregacionId);
        $programa->load('partes');
        $service->guardarHistorial($programa);

        return redirect()->route('reuniones.index')->with('success', 'Programa publicado correctamente.');
    }

    public function destroy(ReunionPrograma $reunione)
    {
        $programa = $reunione;
        $congregacionId = session('congregacion_activa_id');

        if ($programa->congregacion_id !== (int)$congregacionId) {
            abort(403);
        }

        $programa->delete();

        return redirect()->route('reuniones.index')->with('success', 'Programa eliminado.');
    }

    public function finSemana()
    {
        $congregacionId = session('congregacion_activa_id');

        return view('reuniones.finsemana', compact('congregacionId'));
    }

    public function asignaciones()
    {
        $congregacionId = session('congregacion_activa_id');

        $publicadores = Publicador::where('congregacion_id', $congregacionId)
            ->where('activo', true)
            ->whereNotNull('genero')
            ->where('excluido_reuniones', false)
            ->orderBy('nombre')
            ->get();

        // Contar asignaciones por publicador en los ultimos 3 meses
        $desde = Carbon::now()->subMonths(3);
        $historial = \App\Models\ReunionHistorial::where('congregacion_id', $congregacionId)
            ->where('fecha_semana', '>=', $desde)
            ->get()
            ->groupBy('publicador_id');

        $estadisticas = $publicadores->map(function ($pub) use ($historial) {
            $asignaciones = $historial->get($pub->id);
            return [
                'publicador' => $pub,
                'total' => $asignaciones ? $asignaciones->count() : 0,
                'ultima' => $asignaciones ? $asignaciones->max('fecha_semana') : null,
            ];
        })->sortBy('total');

        return view('reuniones.asignaciones', compact('estadisticas'));
    }

    public function recomendar(ReunionPrograma $reunione, string $tipoParte)
    {
        $programa = $reunione;
        $congregacionId = session('congregacion_activa_id');

        if ($programa->congregacion_id !== (int)$congregacionId) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $publicadores = Publicador::where('congregacion_id', $congregacionId)
            ->where('activo', true)
            ->whereNotNull('genero')
            ->where('excluido_reuniones', false)
            ->get()
            ->filter(fn($p) => $p->puedeHacerParte($tipoParte));

        if ($publicadores->isEmpty()) {
            return response()->json(['candidatos' => []]);
        }

        // Cargar TODO el historial hasta la fecha del programa (incluye futuros ya asignados)
        $historial = \App\Models\ReunionHistorial::where('congregacion_id', $congregacionId)
            ->where('fecha_semana', '<=', $programa->fecha_semana)
            ->get();

        // Tambien cargar asignaciones de programas ya creados (que aun no estan en historial)
        $programasHastaFecha = ReunionPrograma::where('congregacion_id', $congregacionId)
            ->where('fecha_semana', '<=', $programa->fecha_semana)
            ->with('partes')
            ->get();

        // Contar asignaciones por publicador (global y por tipo)
        $conteosGlobal = [];
        $conteosTipo = [];
        $ultimaPorTipo = [];
        $asignadosEstaSemana = [];

        foreach ($historial as $h) {
            $conteosGlobal[$h->publicador_id] = ($conteosGlobal[$h->publicador_id] ?? 0) + 1;
            $key = $h->publicador_id . '-' . $h->tipo_parte;
            $conteosTipo[$key] = ($conteosTipo[$key] ?? 0) + 1;
            if (!isset($ultimaPorTipo[$key]) || $h->fecha_semana->gt($ultimaPorTipo[$key])) {
                $ultimaPorTipo[$key] = $h->fecha_semana;
            }
        }

        // Contar asignaciones de esta misma semana desde programas
        foreach ($programasHastaFecha as $prog) {
            if ($prog->fecha_semana->eq($programa->fecha_semana)) {
                $ids = array_filter([
                    $prog->presidente_id, $prog->oracion_inicio_id, $prog->oracion_final_id,
                    $prog->conductor_estudio_id, $prog->lector_estudio_id,
                ]);
                foreach ($ids as $id) {
                    $asignadosEstaSemana[$id] = ($asignadosEstaSemana[$id] ?? 0) + 1;
                }
                foreach ($prog->partes as $parte) {
                    if ($parte->publicador_id) {
                        $asignadosEstaSemana[$parte->publicador_id] = ($asignadosEstaSemana[$parte->publicador_id] ?? 0) + 1;
                    }
                    if ($parte->ayudante_id) {
                        $asignadosEstaSemana[$parte->ayudante_id] = ($asignadosEstaSemana[$parte->ayudante_id] ?? 0) + 1;
                    }
                }
            }
        }

        $fechaSemana = $programa->fecha_semana;
        $numCandidatos = $publicadores->count();

        // Media por tipo
        $conteosTipoArr = $publicadores->map(fn($p) => $conteosTipo[$p->id . '-' . $tipoParte] ?? 0);
        $mediaTipo = $conteosTipoArr->avg() ?: 0;
        // Media global
        $conteosGlobalArr = $publicadores->map(fn($p) => $conteosGlobal[$p->id] ?? 0);
        $mediaGlobal = $conteosGlobalArr->avg() ?: 0;

        $candidatos = $publicadores->map(function ($p) use ($conteosGlobal, $conteosTipo, $ultimaPorTipo, $asignadosEstaSemana, $mediaTipo, $mediaGlobal, $tipoParte, $fechaSemana, $numCandidatos) {
            $puntuacion = 0;

            // Rotacion por tipo
            $key = $p->id . '-' . $tipoParte;
            $ultima = $ultimaPorTipo[$key] ?? null;
            if ($ultima) {
                $dias = $ultima->diffInDays($fechaSemana);
                $diasIdeal = $numCandidatos * 7;
                $puntuacion += min($dias / max($diasIdeal, 1) * 25, 30);
            } else {
                $puntuacion += 35;
            }

            // Equidad por tipo
            $conteoTipo = $conteosTipo[$key] ?? 0;
            $puntuacion += ($mediaTipo - $conteoTipo) * 12;

            // Equidad global
            $conteoTotal = $conteosGlobal[$p->id] ?? 0;
            $puntuacion += ($mediaGlobal - $conteoTotal) * 5;

            // Penalizar si ya tiene parte esta semana
            $estaSemana = $asignadosEstaSemana[$p->id] ?? 0;
            if ($estaSemana > 0) $puntuacion -= 20 * $estaSemana;

            // Penalizar mismo tipo semana pasada
            if ($ultima && $ultima->diffInDays($fechaSemana) <= 7) $puntuacion -= 50;

            return [
                'id' => $p->id,
                'nombre' => $p->nombre_completo,
                'puntuacion' => round($puntuacion, 1),
                'total_asignaciones' => $conteoTotal,
                'asignaciones_tipo' => $conteoTipo,
                'dias_desde_ultima' => $ultima ? $ultima->diffInDays($fechaSemana) : null,
                'esta_semana' => $estaSemana,
            ];
        })->sortByDesc('puntuacion')->values()->take(5);

        return response()->json(['candidatos' => $candidatos]);
    }

    public function historialPublicador(Publicador $publicador)
    {
        $congregacionId = session('congregacion_activa_id');

        if ($publicador->congregacion_id !== (int)$congregacionId) {
            abort(403);
        }

        $historial = \App\Models\ReunionHistorial::where('publicador_id', $publicador->id)
            ->orderByDesc('fecha_semana')
            ->with('programa.partes')
            ->get()
            ->map(function ($h) {
                // Buscar el titulo de la parte en el programa
                $titulo = null;
                if ($h->programa) {
                    $parte = $h->programa->partes->first(function ($p) use ($h) {
                        return $p->tipo === $h->tipo_parte && ($p->publicador_id === $h->publicador_id || $p->ayudante_id === $h->publicador_id);
                    });
                    $titulo = $parte?->titulo;
                }
                $h->titulo_parte = $titulo;
                return $h;
            });

        return view('reuniones.historial', compact('publicador', 'historial'));
    }

    public function generosBulk()
    {
        $congregacionId = session('congregacion_activa_id');

        $publicadores = Publicador::where('congregacion_id', $congregacionId)
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        return view('reuniones.generos', compact('publicadores'));
    }

    /**
     * Tipos de parte disponibles para autorizaciones
     */
    private static array $tiposParte = [
        'presidente', 'oracion', 'tesoros', 'perlas', 'lectura',
        'maestros', 'discurso_maestros', 'discurso_vida', 'conductor_estudio', 'lector_estudio',
    ];

    public function autorizaciones()
    {
        $congregacionId = session('congregacion_activa_id');

        // Auto-seed si la tabla esta vacia para esta congregacion
        $existe = ReunionAutorizacion::where('congregacion_id', $congregacionId)->exists();
        if (!$existe) {
            $this->seedAutorizaciones($congregacionId);
        }

        $publicadores = Publicador::where('congregacion_id', $congregacionId)
            ->where('activo', true)
            ->whereNotNull('genero')
            ->orderBy('nombre')
            ->get();

        $excluidos = Publicador::where('congregacion_id', $congregacionId)
            ->where('activo', true)
            ->where('excluido_reuniones', true)
            ->orderBy('nombre')
            ->get();

        $activos = $publicadores->where('excluido_reuniones', false);

        // Cargar autorizaciones de la tabla
        $autorizacionesDB = ReunionAutorizacion::where('congregacion_id', $congregacionId)->get();
        $porTipo = $autorizacionesDB->groupBy('tipo_parte');

        $autorizaciones = [];
        foreach (self::$tiposParte as $tipo) {
            $ids = $porTipo->has($tipo) ? $porTipo[$tipo]->pluck('publicador_id') : collect();
            $autorizaciones[$tipo] = $activos->filter(fn($p) => $ids->contains($p->id))->values();
        }
        $autorizaciones['excluidos'] = $excluidos;

        // Pool: TODOS los activos no excluidos (fuente constante)
        $pool = $activos;

        // Pool data para el modal JS
        $poolJson = $activos->map(function ($p) {
            return [
                'id' => $p->id,
                'nombre' => $p->nombre_completo,
                'genero' => $p->genero,
                'anciano' => $p->es_anciano,
                'sm' => $p->es_siervo_ministerial,
            ];
        })->values();

        return view('reuniones.autorizaciones', compact('autorizaciones', 'pool', 'poolJson'));
    }

    /**
     * Seed autorizaciones para un publicador individual (al des-excluir)
     */
    private function seedAutorizacionesPublicador(Publicador $pub, int $congregacionId): void
    {
        $tipos = [];

        if ($pub->es_anciano) $tipos[] = 'presidente';
        if ($pub->esHermano() && !$pub->es_menor) $tipos[] = 'oracion';
        if ($pub->esHermano() && ($pub->es_anciano || $pub->es_siervo_ministerial)) {
            $tipos[] = 'tesoros';
            $tipos[] = 'perlas';
            $tipos[] = 'discurso_vida';
        }
        if ($pub->esHermano()) $tipos[] = 'lectura';
        if ($pub->esHermano()) $tipos[] = 'discurso_maestros';
        $tipos[] = 'maestros';
        if ($pub->puede_dirigir_estudio) $tipos[] = 'conductor_estudio';
        if ($pub->puede_leer_estudio || $pub->es_anciano || $pub->es_siervo_ministerial) $tipos[] = 'lector_estudio';

        foreach ($tipos as $tipo) {
            ReunionAutorizacion::firstOrCreate([
                'publicador_id' => $pub->id,
                'tipo_parte' => $tipo,
            ], [
                'congregacion_id' => $congregacionId,
            ]);
        }
    }

    /**
     * Auto-rellenar autorizaciones basandose en la logica de nombramientos
     */
    private function seedAutorizaciones(int $congregacionId): void
    {
        $publicadores = Publicador::where('congregacion_id', $congregacionId)
            ->where('activo', true)
            ->whereNotNull('genero')
            ->where('excluido_reuniones', false)
            ->get();

        $inserts = [];
        $now = now();

        foreach ($publicadores as $pub) {
            $tipos = [];

            if ($pub->es_anciano) $tipos[] = 'presidente';
            if ($pub->esHermano() && !$pub->es_menor) $tipos[] = 'oracion';
            if ($pub->esHermano() && ($pub->es_anciano || $pub->es_siervo_ministerial)) {
                $tipos[] = 'tesoros';
                $tipos[] = 'perlas';
                $tipos[] = 'discurso_vida';
            }
            if ($pub->esHermano()) $tipos[] = 'lectura';
            if ($pub->esHermano()) $tipos[] = 'discurso_maestros';
            $tipos[] = 'maestros';
            if ($pub->puede_dirigir_estudio) $tipos[] = 'conductor_estudio';
            // Lector: ancianos, SM, o cualquiera marcado con puede_leer_estudio
            if ($pub->puede_leer_estudio || $pub->es_anciano || $pub->es_siervo_ministerial) $tipos[] = 'lector_estudio';

            foreach ($tipos as $tipo) {
                $inserts[] = [
                    'publicador_id' => $pub->id,
                    'congregacion_id' => $congregacionId,
                    'tipo_parte' => $tipo,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        // Insertar en lotes
        foreach (array_chunk($inserts, 500) as $chunk) {
            ReunionAutorizacion::insert($chunk);
        }
    }

    public function guardarAutorizacion(Request $request)
    {
        $tiposValidos = implode(',', array_merge(self::$tiposParte, ['excluido_reuniones', 'pool']));

        $request->validate([
            'publicador_id' => 'required|integer',
            'campo' => 'required|string|in:' . $tiposValidos,
            'origen' => 'nullable|string|in:' . $tiposValidos,
        ]);

        $congregacionId = session('congregacion_activa_id');
        $pub = Publicador::where('id', $request->publicador_id)
            ->where('congregacion_id', $congregacionId)
            ->firstOrFail();

        $campo = $request->campo;
        $origen = $request->origen;

        // CASO 1: Arrastrar al pool = quitar del panel de origen
        if ($campo === 'pool') {
            if ($origen && $origen !== 'pool' && $origen !== 'excluido_reuniones') {
                ReunionAutorizacion::where('publicador_id', $pub->id)
                    ->where('tipo_parte', $origen)
                    ->delete();
            }
            if ($origen === 'excluido_reuniones') {
                $pub->excluido_reuniones = false;
                $pub->save();
            }
            return response()->json(['success' => true]);
        }

        // CASO 2: Arrastrar a excluidos = excluir y quitar de todos los paneles
        if ($campo === 'excluido_reuniones') {
            $pub->excluido_reuniones = true;
            $pub->save();
            ReunionAutorizacion::where('publicador_id', $pub->id)->delete();
            return response()->json(['success' => true]);
        }

        // CASO 3: Arrastrar del pool o de otro panel a un panel = AGREGAR (sin quitar del origen)
        if ($origen === 'excluido_reuniones') {
            $pub->excluido_reuniones = false;
            $pub->save();
            // Re-seed autorizaciones basicas al des-excluir
            $this->seedAutorizacionesPublicador($pub, $congregacionId);
        }

        ReunionAutorizacion::firstOrCreate([
            'publicador_id' => $pub->id,
            'tipo_parte' => $campo,
        ], [
            'congregacion_id' => $congregacionId,
        ]);

        return response()->json(['success' => true]);
    }

    public function guardarGeneros(Request $request)
    {
        $request->validate([
            'generos' => 'required|array',
            'generos.*' => 'nullable|in:M,F',
        ]);

        foreach ($request->generos as $id => $genero) {
            Publicador::where('id', $id)->update(['genero' => $genero ?: null]);
        }

        return redirect()->route('reuniones.generos')->with('success', 'Generos actualizados correctamente.');
    }
}
