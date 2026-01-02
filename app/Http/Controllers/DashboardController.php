<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Territorio;
use App\Models\Publicador;
use App\Models\Registro;
use App\Models\Congregacion;
use App\Models\GrupoPredicacion;
use App\Models\TurnoGenerado;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Obtener la congregacion activa
        $congregacionId = session('congregacion_activa_id');
        $congregacion = Congregacion::find($congregacionId);

        // Datos basicos y seguros
        $data = [
            'congregacion' => $congregacion,
            'totalTerritorios' => 0,
            'publicadoresActivos' => 0,
            'totalRegistros' => 0,
            'territoriosLibres' => 0,
            'territoriosActivos' => 0,
            'registrosActivos' => collect([]),
            'territoriosAtrasados' => 0,
            'territoriosArchivo' => 0,
            'totalGrupos' => 0,
            'turnosEsteMes' => 0,
            'publicadoresAprobados' => 0
        ];

        try {
            // Filtrar por congregacion si existe
            if ($congregacionId) {
                $data['totalTerritorios'] = Territorio::where('congregacion_id', $congregacionId)->count();
                $data['publicadoresActivos'] = Publicador::where('congregacion_id', $congregacionId)->where('activo', true)->count();
                $data['totalRegistros'] = Registro::whereHas('territorio', function($q) use ($congregacionId) {
                    $q->where('congregacion_id', $congregacionId);
                })->count();
                $data['totalGrupos'] = GrupoPredicacion::where('congregacion_id', $congregacionId)->count();

                $allTerritorios = Territorio::where('congregacion_id', $congregacionId)->get();

                // Estadisticas PPOC
                $data['publicadoresAprobados'] = Publicador::where('congregacion_id', $congregacionId)
                    ->where('aprobado_ppoc', true)
                    ->where('activo', true)
                    ->count();

                // Turnos de este mes
                $inicioMes = Carbon::now()->startOfMonth();
                $finMes = Carbon::now()->endOfMonth();
                $data['turnosEsteMes'] = TurnoGenerado::where('congregacion_id', $congregacionId)
                    ->whereBetween('fecha', [$inicioMes, $finMes])
                    ->count();

            } else {
                $data['totalTerritorios'] = Territorio::count();
                $data['publicadoresActivos'] = Publicador::where('activo', true)->count();
                $data['totalRegistros'] = Registro::count();
                $data['totalGrupos'] = GrupoPredicacion::count();
                $allTerritorios = Territorio::all();
            }

            // Calcular estadisticas de estados
            $estadisticas = [
                'libre' => 0,
                'activo' => 0,
                'atrasado' => 0,
                'archivo' => 0
            ];

            foreach ($allTerritorios as $territorio) {
                $estado = $territorio->calcularEstado();
                if (isset($estadisticas[$estado])) {
                    $estadisticas[$estado]++;
                }
            }

            $data['territoriosLibres'] = $estadisticas['libre'];
            $data['territoriosActivos'] = $estadisticas['activo'];
            $data['territoriosAtrasados'] = $estadisticas['atrasado'];
            $data['territoriosArchivo'] = $estadisticas['archivo'];

            // Territorios realmente disponibles para asignar
            $data['territoriosDisponibles'] = $allTerritorios->filter(function($territorio) {
                return $territorio->estaDisponibleParaAsignar();
            })->count();

            // Registros activos
            $registrosQuery = Registro::with(['territorio', 'publicador'])
                ->whereNull('fecha_entrada')
                ->latest('fecha_salida');

            if ($congregacionId) {
                $registrosQuery->whereHas('territorio', function($q) use ($congregacionId) {
                    $q->where('congregacion_id', $congregacionId);
                });
            }

            $data['registrosActivos'] = $registrosQuery->take(5)->get();

        } catch (\Exception $e) {
            \Log::error('Error en DashboardController: ' . $e->getMessage());
        }

        return response()->view('dashboard', $data);
    }

    /**
     * Mostrar pagina de configuracion
     */
    public function configuracion()
    {
        $congregacionId = session('congregacion_activa_id');
        $congregacion = Congregacion::find($congregacionId);

        return view('configuracion', compact('congregacion'));
    }

    /**
     * Guardar configuracion de la congregacion
     */
    public function guardarConfiguracion(Request $request)
    {
        $request->validate([
            'dias_limite_activo' => 'required|integer|min:1|max:365',
            'dias_archivo' => 'required|integer|min:1|max:365',
            'dias_limite_activo_campana' => 'nullable|integer|min:1|max:365',
            'dias_archivo_campana' => 'nullable|integer|min:1|max:365',
            'dias_limite_activo_negocios' => 'nullable|integer|min:1|max:365',
            'dias_archivo_negocios' => 'nullable|integer|min:1|max:365',
        ]);

        $congregacionId = session('congregacion_activa_id');
        $congregacion = Congregacion::find($congregacionId);

        if (!$congregacion) {
            return redirect()->route('configuracion')
                ->with('error', 'No se encontro la congregacion activa.');
        }

        $congregacion->update([
            'dias_limite_activo' => $request->dias_limite_activo,
            'dias_archivo' => $request->dias_archivo,
            'dias_limite_activo_campana' => $request->dias_limite_activo_campana ?? 30,
            'dias_archivo_campana' => $request->dias_archivo_campana ?? 30,
            'dias_limite_activo_negocios' => $request->dias_limite_activo_negocios ?? 60,
            'dias_archivo_negocios' => $request->dias_archivo_negocios ?? 60,
        ]);

        return redirect()->route('configuracion')
            ->with('success', 'Configuracion de ' . $congregacion->nombre . ' actualizada correctamente.');
    }

    /**
     * Guardar mensaje de WhatsApp personalizado
     */
    public function guardarMensajeWhatsapp(Request $request)
    {
        $request->validate([
            'mensaje_whatsapp' => 'required|string|max:2000'
        ]);

        $congregacionId = session('congregacion_activa_id');
        $congregacion = Congregacion::find($congregacionId);

        if (!$congregacion) {
            return redirect()->route('configuracion')
                ->with('error', 'No se encontro la congregacion activa.');
        }

        $congregacion->update([
            'mensaje_whatsapp' => $request->mensaje_whatsapp,
        ]);

        return redirect()->route('configuracion')
            ->with('success', 'Mensaje de WhatsApp actualizado correctamente.');
    }
}
