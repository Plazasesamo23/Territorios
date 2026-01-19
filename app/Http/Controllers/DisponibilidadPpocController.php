<?php

namespace App\Http\Controllers;

use App\Models\Turno;
use App\Models\DisponibilidadPpoc;
use App\Models\Publicador;
use App\Models\Congregacion;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class DisponibilidadPpocController extends Controller
{
    /**
     * Mostrar formulario publico de disponibilidad
     */
    public function form($token)
    {
        // Log para depuración
        Log::info('Acceso a disponibilidad', [
            'token' => $token,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);

        $congregacion = Congregacion::where('token_disponibilidad', $token)->first();

        if (!$congregacion) {
            Log::warning('Token de disponibilidad no encontrado', ['token' => $token]);
            return response()->view('errors.token-invalido', [
                'mensaje' => 'El enlace no es válido o ha expirado.',
                'sugerencia' => 'Solicita un nuevo enlace al administrador de tu congregación.'
            ], 404);
        }

        $turnos = Turno::forCongregacion($congregacion->id)
            ->where('activo', true)
            ->orderBy('dia_semana')
            ->orderBy('hora_inicio')
            ->get();

        // Solo publicadores aprobados para PPOC
        $publicadores = Publicador::forCongregacion($congregacion->id)
            ->where('aprobado_ppoc', true)
            ->where('activo', true)
            ->orderBy('nombre')
            ->orderBy('apellidos')
            ->get();

        return view('ppoc.disponibilidad.form', compact('congregacion', 'turnos', 'publicadores'));
    }

    /**
     * Cargar disponibilidad actual de un publicador (AJAX)
     */
    public function getDisponibilidad($token, $publicadorId)
    {
        $congregacion = Congregacion::where('token_disponibilidad', $token)->first();

        if (!$congregacion) {
            return response()->json(['error' => 'Token inválido'], 404);
        }

        $publicador = Publicador::forCongregacion($congregacion->id)
            ->where('id', $publicadorId)
            ->first();

        if (!$publicador) {
            return response()->json(['error' => 'Publicador no encontrado'], 404);
        }

        $turnosIds = DisponibilidadPpoc::where('publicador_id', $publicador->id)
            ->pluck('turno_id')
            ->toArray();

        return response()->json(['turnos' => $turnosIds]);
    }

    /**
     * Guardar disponibilidad desde formulario publico
     */
    public function store(Request $request, $token)
    {
        $congregacion = Congregacion::where('token_disponibilidad', $token)->first();

        if (!$congregacion) {
            Log::warning('Intento de guardar con token inválido', ['token' => $token]);
            return back()->with('error', 'El enlace no es válido. Solicita uno nuevo al administrador.');
        }

        $request->validate([
            'publicador_id' => ['required', 'exists:publicadores,id'],
            'turnos' => ['nullable', 'array'],
            'turnos.*' => ['exists:turnos,id'],
        ], [
            'publicador_id.required' => 'Selecciona tu nombre.',
        ]);

        $publicador = Publicador::findOrFail($request->publicador_id);

        // Verificar que el publicador pertenece a esta congregacion
        if ($publicador->congregacion_id !== $congregacion->id) {
            abort(403);
        }

        // Eliminar disponibilidades anteriores de este publicador para esta congregacion
        $turnoIds = Turno::where('congregacion_id', $congregacion->id)->pluck('id');
        DisponibilidadPpoc::where('publicador_id', $publicador->id)
            ->whereIn('turno_id', $turnoIds)
            ->delete();

        // Guardar nuevas disponibilidades (si hay)
        if ($request->turnos && count($request->turnos) > 0) {
            foreach ($request->turnos as $turnoId) {
                // Verificar que el turno pertenece a esta congregacion
                $turno = Turno::where('id', $turnoId)
                    ->where('congregacion_id', $congregacion->id)
                    ->first();

                if ($turno) {
                    DisponibilidadPpoc::create([
                        'publicador_id' => $publicador->id,
                        'turno_id' => $turnoId,
                    ]);
                }
            }
        }

        Log::info('Disponibilidad guardada', [
            'publicador' => $publicador->nombre,
            'turnos' => count($request->turnos ?? [])
        ]);

        $mensaje = count($request->turnos ?? []) > 0
            ? 'Disponibilidad guardada correctamente. Gracias ' . $publicador->nombre . '!'
            : 'Se ha borrado tu disponibilidad anterior, ' . $publicador->nombre . '.';

        return redirect()->route('disponibilidad.form', $token)
            ->with('success', $mensaje);
    }

    /**
     * Ver disponibilidad por turno (para admin)
     */
    public function porTurno()
    {
        $congregacionId = session('congregacion_activa_id');
        $congregacion = Congregacion::find($congregacionId);

        // Generar token si no existe
        if (!$congregacion->token_disponibilidad) {
            $congregacion->token_disponibilidad = Str::random(32);
            $congregacion->save();
        }

        $turnos = Turno::where('congregacion_id', $congregacionId)
            ->where('activo', true)
            ->orderBy('dia_semana')
            ->orderBy('hora_inicio')
            ->get();

        // Cargar disponibilidades
        foreach ($turnos as $turno) {
            $turno->publicadoresDisponibles = Publicador::whereHas('disponibilidadesPpoc', function($q) use ($turno) {
                $q->where('turno_id', $turno->id);
            })
            ->orderBy('nombre')
            ->orderBy('apellidos')
            ->get();
        }

        // Agrupar por dia
        $turnosPorDia = $turnos->groupBy('dia_semana');

        return view('ppoc.disponibilidad.por-turno', compact('turnosPorDia', 'congregacion'));
    }

    /**
     * Ver disponibilidad por publicador (para admin)
     */
    public function porPublicador()
    {
        $congregacionId = session('congregacion_activa_id');

        $publicadores = Publicador::where('congregacion_id', $congregacionId)
            ->where('aprobado_ppoc', true)
            ->where('activo', true)
            ->orderBy('nombre')
            ->orderBy('apellidos')
            ->with(['disponibilidadesPpoc.turno'])
            ->get();

        return view('ppoc.disponibilidad.por-publicador', compact('publicadores'));
    }
}
