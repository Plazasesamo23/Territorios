<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\GrupoPredicacion;
use App\Models\Publicador;
use App\Models\Registro;
use App\Models\GrupoHistorico;
use App\Services\GeneradorGruposService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class GrupoPredicacionController extends Controller
{
    public function index()
    {
        $congregacionId = session('congregacion_activa_id');

        // Obtener o crear los 6 grupos
        for ($i = 1; $i <= 6; $i++) {
            GrupoPredicacion::firstOrCreate(
                ['congregacion_id' => $congregacionId, 'numero' => $i],
                ['nombre' => 'Grupo ' . $i]
            );
        }

        $grupos = GrupoPredicacion::where('congregacion_id', $congregacionId)
            ->with(['publicadores' => function($q) {
                $q->orderBy('orden_grupo', 'asc');
            }])
            ->orderBy('numero')
            ->get();

        $sinGrupo = Publicador::where('congregacion_id', $congregacionId)
            ->whereNull('grupo_predicacion_id')
            ->where('activo', true)
            ->orderBy('orden_grupo', 'asc')
            ->get();

        return view('grupos-predicacion.index', compact('grupos', 'sinGrupo'));
    }

    public function moverPublicador(Request $request)
    {
        $request->validate([
            'publicador_id' => 'required|exists:publicadores,id',
            'grupo_id' => 'nullable|exists:grupos_predicacion,id',
            'orden' => 'nullable|integer',
        ]);

        $publicador = Publicador::findOrFail($request->publicador_id);
        $nuevoGrupoId = $request->grupo_id ?: null;
        $nuevoOrden = $request->orden ?? 0;

        // Si cambia de grupo, quitar roles de SUP y AUX
        if ($publicador->grupo_predicacion_id != $nuevoGrupoId) {
            $publicador->es_superintendente = false;
            $publicador->es_auxiliar = false;
        }

        $publicador->grupo_predicacion_id = $nuevoGrupoId;
        $publicador->orden_grupo = $nuevoOrden;
        $publicador->save();

        return response()->json(['success' => true]);
    }

    public function reordenar(Request $request)
    {
        $request->validate([
            'orden' => 'required|array',
        ]);

        foreach ($request->orden as $index => $pubId) {
            Publicador::where('id', $pubId)->update(['orden_grupo' => $index]);
        }

        return response()->json(['success' => true]);
    }

    public function toggleRol(Request $request)
    {
        $request->validate([
            'publicador_id' => 'required|exists:publicadores,id',
            'rol' => 'required|in:superintendente,auxiliar,precursor',
        ]);

        $publicador = Publicador::findOrFail($request->publicador_id);

        switch ($request->rol) {
            case 'superintendente':
                // Solo un superintendente por grupo
                if (!$publicador->es_superintendente && $publicador->grupo_predicacion_id) {
                    Publicador::where('grupo_predicacion_id', $publicador->grupo_predicacion_id)
                        ->update(['es_superintendente' => false]);
                }
                $publicador->es_superintendente = !$publicador->es_superintendente;
                break;
            case 'auxiliar':
                $publicador->es_auxiliar = !$publicador->es_auxiliar;
                break;
            case 'precursor':
                $publicador->es_precursor = !$publicador->es_precursor;
                break;
        }

        $publicador->save();

        return response()->json([
            'success' => true,
            'es_superintendente' => $publicador->es_superintendente,
            'es_auxiliar' => $publicador->es_auxiliar,
            'es_precursor' => $publicador->es_precursor,
        ]);
    }

    /**
     * Obtener estadisticas de un publicador
     */
    public function estadisticas($id)
    {
        $publicador = Publicador::with('grupoPredicacion')->findOrFail($id);

        // Obtener todos los registros del publicador
        $registros = Registro::with('territorio')
            ->where('publicador_id', $id)
            ->orderBy('fecha_salida', 'desc')
            ->get();

        // Estadisticas generales
        $totalTerritorios = $registros->count();
        $territoriosActivos = $registros->whereNull('fecha_entrada')->count();
        $territoriosCompletados = $registros->whereNotNull('fecha_entrada')->count();

        // Calcular dias totales predicando
        $diasTotales = 0;
        foreach ($registros as $reg) {
            if ($reg->fecha_entrada) {
                $diasTotales += $reg->fecha_salida->diffInDays($reg->fecha_entrada);
            } else {
                $diasTotales += $reg->fecha_salida->diffInDays(now());
            }
        }

        // Promedio de dias por territorio
        $promedioDias = $territoriosCompletados > 0
            ? round($diasTotales / $territoriosCompletados)
            : 0;

        // Territorios por tipo
        $porTipo = $registros->groupBy(function($reg) {
            return $reg->territorio->tipo ?? 'Sin tipo';
        })->map->count();

        // Ultimos 5 registros
        $ultimosRegistros = $registros->take(5)->map(function($reg) {
            return [
                'territorio' => $reg->territorio->numero ?? 'N/A',
                'territorio_nombre' => $reg->territorio->nombre ?? '',
                'tipo' => $reg->territorio->tipo ?? 'N/A',
                'fecha_salida' => $reg->fecha_salida->format('d/m/Y'),
                'fecha_entrada' => $reg->fecha_entrada ? $reg->fecha_entrada->format('d/m/Y') : 'En curso',
                'dias' => $reg->fecha_entrada
                    ? $reg->fecha_salida->diffInDays($reg->fecha_entrada)
                    : $reg->fecha_salida->diffInDays(now()) . ' (activo)',
            ];
        });

        // Territorio activo actual
        $territorioActivo = $registros->whereNull('fecha_entrada')->first();

        return response()->json([
            'publicador' => [
                'id' => $publicador->id,
                'nombre' => $publicador->nombre,
                'apellidos' => $publicador->apellidos,
                'nombre_completo' => $publicador->nombre_completo,
                'telefono' => $publicador->telefono,
                'notas' => $publicador->notas,
                'grupo' => $publicador->grupoPredicacion ? 'Grupo ' . $publicador->grupoPredicacion->numero : 'Sin grupo',
                'es_precursor' => $publicador->es_precursor,
                'es_superintendente' => $publicador->es_superintendente,
                'es_auxiliar' => $publicador->es_auxiliar,
                'aprobado_ppoc' => $publicador->aprobado_ppoc,
            ],
            'estadisticas' => [
                'total_territorios' => $totalTerritorios,
                'territorios_activos' => $territoriosActivos,
                'territorios_completados' => $territoriosCompletados,
                'dias_totales' => $diasTotales,
                'promedio_dias' => $promedioDias,
                'por_tipo' => $porTipo,
            ],
            'territorio_activo' => $territorioActivo ? [
                'numero' => $territorioActivo->territorio->numero ?? 'N/A',
                'nombre' => $territorioActivo->territorio->nombre ?? '',
                'tipo' => $territorioActivo->territorio->tipo ?? 'N/A',
                'fecha_salida' => $territorioActivo->fecha_salida->format('d/m/Y'),
                'dias' => $territorioActivo->fecha_salida->diffInDays(now()),
            ] : null,
            'ultimos_registros' => $ultimosRegistros,
        ]);
    }

    /**
     * Guardar historial del ano actual
     */
    public function guardarHistorial(Request $request)
    {
        $congregacionId = session('congregacion_activa_id');
        $ano = $request->ano_servicio ?? $this->getAnoServicioActual();

        // Verificar si ya existe
        $existe = GrupoHistorico::where('congregacion_id', $congregacionId)
            ->where('ano_servicio', $ano)
            ->exists();

        if ($existe) {
            return response()->json(['error' => 'Ya existe historial para este ano'], 400);
        }

        // Guardar estado actual
        $grupos = GrupoPredicacion::where('congregacion_id', $congregacionId)
            ->with('publicadores')
            ->get();

        foreach ($grupos as $grupo) {
            foreach ($grupo->publicadores as $pub) {
                $rol = 'normal';
                if ($pub->es_superintendente) $rol = 'superintendente';
                elseif ($pub->es_auxiliar) $rol = 'auxiliar';
                elseif ($pub->es_precursor) $rol = 'precursor';

                GrupoHistorico::create([
                    'congregacion_id' => $congregacionId,
                    'ano_servicio' => $ano,
                    'grupo_numero' => $grupo->numero,
                    'publicador_id' => $pub->id,
                    'rol' => $rol
                ]);
            }
        }

        return response()->json(['success' => true, 'mensaje' => "Historial $ano guardado"]);
    }

    /**
     * Ver historial de un ano
     */
    public function verHistorial($ano)
    {
        $congregacionId = session('congregacion_activa_id');

        $historial = GrupoHistorico::where('congregacion_id', $congregacionId)
            ->where('ano_servicio', $ano)
            ->with('publicador')
            ->get()
            ->groupBy('grupo_numero');

        $anosDisponibles = GrupoHistorico::anosDisponibles($congregacionId);

        return view('grupos-predicacion.historial', compact('historial', 'ano', 'anosDisponibles'));
    }

    /**
     * Generar grupos automaticos
     */
    public function generarAutomatico(Request $request)
    {
        $congregacionId = session('congregacion_activa_id');

        $servicio = new GeneradorGruposService($congregacionId);
        $nuevosGrupos = $servicio->generar();

        if ($request->preview) {
            // Convertir colecciones a arrays para JSON
            $preview = [];
            foreach ($nuevosGrupos as $num => $publicadores) {
                $preview[$num] = $publicadores->map(function($p) {
                    return [
                        'id' => $p->id,
                        'nombre_completo' => $p->nombre_completo,
                        'es_superintendente' => $p->es_superintendente,
                        'es_auxiliar' => $p->es_auxiliar,
                        'es_precursor' => $p->es_precursor,
                    ];
                })->values();
            }
            return response()->json(['preview' => $preview]);
        }

        // Aplicar cambios
        $gruposDb = GrupoPredicacion::where('congregacion_id', $congregacionId)->get();

        foreach ($nuevosGrupos as $numero => $publicadores) {
            $grupoDb = $gruposDb->firstWhere('numero', $numero);

            foreach ($publicadores as $pub) {
                Publicador::where('id', $pub->id)->update([
                    'grupo_predicacion_id' => $grupoDb->id
                ]);
            }
        }

        return response()->json(['success' => true]);
    }

    /**
     * Obtener ano de servicio actual
     */
    private function getAnoServicioActual()
    {
        $mes = now()->month;
        $ano = now()->year;
        return $mes >= 9 ? "$ano/" . ($ano + 1) : ($ano - 1) . "/$ano";
    }

    /**
     * Listar todos los anos del historial
     */
    public function listarHistorial()
    {
        $congregacionId = session('congregacion_activa_id');

        $anos = GrupoHistorico::where('congregacion_id', $congregacionId)
            ->select('ano_servicio')
            ->selectRaw('COUNT(*) as total_publicadores')
            ->groupBy('ano_servicio')
            ->orderBy('ano_servicio', 'desc')
            ->get();

        $anoActual = $this->getAnoServicioActual();

        return view('grupos-predicacion.historial', compact('anos', 'anoActual'));
    }

    /**
     * Crear un nuevo ano en el historial
     */
    public function crearAnoHistorial(Request $request)
    {
        $congregacionId = session('congregacion_activa_id');
        $anoServicio = $request->ano_servicio;

        // Verificar formato
        if (!preg_match('/^\d{4}\/\d{4}$/', $anoServicio)) {
            return response()->json(['error' => 'Formato de ano invalido'], 400);
        }

        // Verificar si ya existe
        $existe = GrupoHistorico::where('congregacion_id', $congregacionId)
            ->where('ano_servicio', $anoServicio)
            ->exists();

        if ($existe) {
            return response()->json(['error' => 'Este ano ya existe'], 400);
        }

        // No creamos registros vacios, el ano se crea cuando se agregan publicadores
        return response()->json(['success' => true, 'ano' => $anoServicio]);
    }

    /**
     * Editar historial de un ano especifico
     */
    public function editarHistorial($ano)
    {
        $congregacionId = session('congregacion_activa_id');

        // Obtener historial del ano agrupado por grupo
        $historial = GrupoHistorico::where('congregacion_id', $congregacionId)
            ->where('ano_servicio', $ano)
            ->with('publicador')
            ->get()
            ->groupBy('grupo_numero');

        // Obtener IDs de publicadores ya asignados en este ano
        $asignadosIds = GrupoHistorico::where('congregacion_id', $congregacionId)
            ->where('ano_servicio', $ano)
            ->pluck('publicador_id')
            ->toArray();

        // Publicadores disponibles (no asignados en este ano)
        $publicadoresDisponibles = Publicador::where('congregacion_id', $congregacionId)
            ->where('activo', true)
            ->whereNotIn('id', $asignadosIds)
            ->orderBy('nombre')
            ->get();

        return view('grupos-predicacion.historial-editar', compact('historial', 'ano', 'publicadoresDisponibles'));
    }

    /**
     * Actualizar un registro del historial
     */
    public function actualizarHistorial(Request $request)
    {
        $congregacionId = session('congregacion_activa_id');
        $anoServicio = $request->ano_servicio;
        $publicadorId = $request->publicador_id;
        $grupoNumero = $request->grupo_numero;
        $rol = $request->rol ?? 'normal';

        // Si grupo es 0 o null, eliminar del historial
        if (!$grupoNumero || $grupoNumero == 0) {
            GrupoHistorico::where('congregacion_id', $congregacionId)
                ->where('ano_servicio', $anoServicio)
                ->where('publicador_id', $publicadorId)
                ->delete();

            return response()->json(['success' => true, 'action' => 'deleted']);
        }

        // Buscar si ya existe
        $registro = GrupoHistorico::where('congregacion_id', $congregacionId)
            ->where('ano_servicio', $anoServicio)
            ->where('publicador_id', $publicadorId)
            ->first();

        if ($registro) {
            // Actualizar
            $registro->grupo_numero = $grupoNumero;
            $registro->rol = $rol;
            $registro->save();
        } else {
            // Crear nuevo
            GrupoHistorico::create([
                'congregacion_id' => $congregacionId,
                'ano_servicio' => $anoServicio,
                'grupo_numero' => $grupoNumero,
                'publicador_id' => $publicadorId,
                'rol' => $rol
            ]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Eliminar un ano completo del historial
     */
    public function eliminarAnoHistorial($ano)
    {
        $congregacionId = session('congregacion_activa_id');

        $deleted = GrupoHistorico::where('congregacion_id', $congregacionId)
            ->where('ano_servicio', $ano)
            ->delete();

        return response()->json(['success' => true, 'deleted' => $deleted]);
    }
}
