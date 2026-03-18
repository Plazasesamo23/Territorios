<?php

namespace App\Http\Controllers\Reuniones;

use App\Http\Controllers\Controller;
use App\Models\ReunionPrograma;
use App\Models\ReunionParte;
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

        $programas = ReunionPrograma::where('congregacion_id', $congregacionId)
            ->orderByDesc('fecha_semana')
            ->paginate(12);

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
            'cantidad_semanas' => 'required|integer|min:1|max:8',
        ]);

        $congregacionId = session('congregacion_activa_id');
        $fechaInicio = Carbon::parse($request->fecha_semana)->startOfWeek(Carbon::MONDAY);
        $cantidad = $request->cantidad_semanas;
        $creados = 0;

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

                AsignacionReunionService::generarPartesEstandar($programa);
                $creados++;
            }
        }

        if ($creados === 0) {
            return redirect()->route('reuniones.index')->with('error', 'Los programas para esas semanas ya existen.');
        }

        return redirect()->route('reuniones.index')->with('success', "Se crearon {$creados} programas correctamente.");
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

        return view('reuniones.edit', compact('programa', 'publicadores', 'sinGenero'));
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
                        'ayudante_id' => $datos['ayudante_id'] ?? null,
                        'titulo' => $datos['titulo'] ?? $parte->titulo,
                        'duracion_minutos' => $datos['duracion_minutos'] ?? $parte->duracion_minutos,
                    ]);
                }
            }
        }

        // Regenerar historial
        $service = new AsignacionReunionService($congregacionId);
        $programa->load('partes');

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

        $msg = "Auto-asignacion completada: {$resultado['asignados']} asignaciones realizadas.";
        if (!empty($resultado['sin_candidatos'])) {
            $msg .= ' Sin candidatos para: ' . implode(', ', $resultado['sin_candidatos']);
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

        // Contar asignaciones por publicador hasta esa fecha
        $conteos = [];
        $ultimaPorTipo = [];
        $asignadosEstaSemana = [];

        foreach ($historial as $h) {
            $conteos[$h->publicador_id] = ($conteos[$h->publicador_id] ?? 0) + 1;
            $key = $h->publicador_id . '-' . $h->tipo_parte;
            if (!isset($ultimaPorTipo[$key]) || $h->fecha_semana->gt($ultimaPorTipo[$key])) {
                $ultimaPorTipo[$key] = $h->fecha_semana;
            }
        }

        // Contar asignaciones directas de programas (para los que no tienen historial aun)
        foreach ($programasHastaFecha as $prog) {
            $ids = array_filter([
                $prog->presidente_id, $prog->oracion_inicio_id, $prog->oracion_final_id,
                $prog->conductor_estudio_id, $prog->lector_estudio_id,
            ]);
            foreach ($ids as $id) {
                $conteos[$id] = ($conteos[$id] ?? 0);
            }
            // Contar los de esta misma semana
            if ($prog->fecha_semana->eq($programa->fecha_semana)) {
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

        $media = count($conteos) > 0 ? array_sum($conteos) / count($conteos) : 0;
        $fechaSemana = $programa->fecha_semana;

        $candidatos = $publicadores->map(function ($p) use ($conteos, $ultimaPorTipo, $asignadosEstaSemana, $media, $tipoParte, $fechaSemana) {
            $puntuacion = 0;
            $conteo = $conteos[$p->id] ?? 0;

            // Dias desde ultima asignacion de este tipo
            $key = $p->id . '-' . $tipoParte;
            $ultima = $ultimaPorTipo[$key] ?? null;
            if ($ultima) {
                $dias = $ultima->diffInDays($fechaSemana);
                $puntuacion += min($dias * 0.5, 30);
            } else {
                $puntuacion += 30;
            }

            // Equidad
            $diferencia = $media - $conteo;
            $puntuacion += $diferencia > 0 ? $diferencia * 8 : $diferencia * 10;

            // Penalizar si ya tiene parte esta semana
            $estaSemana = $asignadosEstaSemana[$p->id] ?? 0;
            if ($estaSemana > 0) $puntuacion -= 15 * $estaSemana;

            // Penalizar mismo tipo semana pasada
            if ($ultima && $ultima->diffInDays($fechaSemana) <= 7) $puntuacion -= 40;

            return [
                'id' => $p->id,
                'nombre' => $p->nombre_completo,
                'puntuacion' => round($puntuacion, 1),
                'total_asignaciones' => $conteo,
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
