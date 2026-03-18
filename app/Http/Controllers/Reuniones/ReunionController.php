<?php

namespace App\Http\Controllers\Reuniones;

use App\Http\Controllers\Controller;
use App\Models\ReunionPrograma;
use App\Models\ReunionParte;
use App\Models\Publicador;
use App\Services\AsignacionReunionService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReunionController extends Controller
{
    public function index()
    {
        $congregacionId = session('congregacion_activa_id');

        $programas = ReunionPrograma::where('congregacion_id', $congregacionId)
            ->orderByDesc('fecha_semana')
            ->paginate(12);

        return view('reuniones.index', compact('programas'));
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
