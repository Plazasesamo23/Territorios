<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Territorio;
use App\Models\Publicador;
use App\Models\Registro;
use App\Models\Congregacion;

class DashboardController extends Controller
{
    public function index()
    {
        // DEBUG: Verificar que este controlador se está ejecutando
        \Log::info('DashboardController::index ejecutándose');

        // Datos básicos y seguros
        $data = [
            'totalTerritorios' => 0,
            'publicadoresActivos' => 0,
            'totalRegistros' => 0,
            'territoriosLibres' => 0,
            'territoriosActivos' => 0,
            'registrosActivos' => collect([]),
            'territoriosAtrasados' => 0,
            'territoriosArchivo' => 0
        ];

        try {
            $data['totalTerritorios'] = Territorio::count();
            $data['publicadoresActivos'] = Publicador::count();
            $data['totalRegistros'] = Registro::count();

            // Calcular estadísticas de estados
            $allTerritorios = Territorio::all();
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

            // Territorios realmente disponibles para asignar (cumplen regla de 90 días)
            $data['territoriosDisponibles'] = $allTerritorios->filter(function($territorio) {
                return $territorio->estaDisponibleParaAsignar();
            })->count();

            // Registros activos
            $data['registrosActivos'] = Registro::with(['territorio', 'publicador'])
                ->whereNull('fecha_entrada')
                ->latest('fecha_salida')
                ->take(5)
                ->get();

        } catch (\Exception $e) {
            \Log::error('Error en DashboardController: ' . $e->getMessage());
        }

        // FORZAR la vista dashboard
        return response()->view('dashboard', $data);
    }

    /**
     * Mostrar página de configuración
     */
    public function configuracion()
    {
        $congregacionId = session('congregacion_activa_id');
        $congregacion = Congregacion::find($congregacionId);

        return view('configuracion', compact('congregacion'));
    }

    /**
     * Guardar configuración de la congregación
     */
    public function guardarConfiguracion(Request $request)
    {
        $request->validate([
            'dias_limite_activo' => 'required|integer|min:1|max:365',
            'dias_archivo' => 'required|integer|min:1|max:365',
            'dias_limite_activo_campana' => 'required|integer|min:1|max:365',
            'dias_archivo_campana' => 'required|integer|min:1|max:365',
            'dias_limite_activo_negocios' => 'required|integer|min:1|max:365',
            'dias_archivo_negocios' => 'required|integer|min:1|max:365',
        ]);

        // Obtener la congregación activa
        $congregacionId = session('congregacion_activa_id');
        $congregacion = Congregacion::find($congregacionId);

        if (!$congregacion) {
            return redirect()->route('configuracion')
                ->with('error', 'No se encontró la congregación activa.');
        }

        // Actualizar los valores en la congregación
        $congregacion->update([
            'dias_limite_activo' => $request->dias_limite_activo,
            'dias_archivo' => $request->dias_archivo,
            'dias_limite_activo_campana' => $request->dias_limite_activo_campana,
            'dias_archivo_campana' => $request->dias_archivo_campana,
            'dias_limite_activo_negocios' => $request->dias_limite_activo_negocios,
            'dias_archivo_negocios' => $request->dias_archivo_negocios,
        ]);

        return redirect()->route('configuracion')
            ->with('success', 'Configuración de ' . $congregacion->nombre . ' actualizada correctamente.');
    }

    /**
     * Guardar mensaje de WhatsApp personalizado
     */
    public function guardarMensajeWhatsapp(Request $request)
    {
        $request->validate([
            'mensaje_whatsapp' => 'required|string|max:2000'
        ]);

        // Obtener la congregación activa
        $congregacionId = session('congregacion_activa_id');
        $congregacion = Congregacion::find($congregacionId);

        if (!$congregacion) {
            return redirect()->route('configuracion')
                ->with('error', 'No se encontró la congregación activa.');
        }

        // Actualizar el mensaje de WhatsApp
        $congregacion->update([
            'mensaje_whatsapp' => $request->mensaje_whatsapp,
        ]);

        return redirect()->route('configuracion')
            ->with('success', 'Mensaje de WhatsApp actualizado correctamente.');
    }
}
