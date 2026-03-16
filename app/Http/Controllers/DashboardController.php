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
        $user = auth()->user();

        // Redireccion por rol
        if ($user->isTerritoriosUser()) {
            return redirect()->route('panel-territorios');
        }
        if ($user->isPpocUser()) {
            return redirect()->route('ppoc.calendario');
        }

        // Obtener la congregacion activa
        $congregacionId = session('congregacion_activa_id');
        $congregacion = Congregacion::find($congregacionId);

        // Modulos disponibles segun permisos
        $modulos = [];

        $modulos[] = [
            'nombre' => 'Territorios',
            'descripcion' => 'Gestionar territorios, asignaciones y estados',
            'ruta' => route('panel-territorios'),
            'icono' => 'territorios',
            'color' => 'green',
        ];

        if ($user->canAccessPPOC()) {
            $modulos[] = [
                'nombre' => 'PPOC',
                'descripcion' => 'Calendario, turnos y asignaciones',
                'ruta' => route('ppoc.calendario'),
                'icono' => 'ppoc',
                'color' => 'blue',
            ];
        }

        if ($user->canGenerateS13()) {
            $modulos[] = [
                'nombre' => 'S-13',
                'descripcion' => 'Reportes S-13, PDF y vista previa',
                'ruta' => route('s13.index'),
                'icono' => 's13',
                'color' => 'purple',
            ];
        }

        if ($user->isAdmin()) {
            $modulos[] = [
                'nombre' => 'Administracion',
                'descripcion' => 'Publicadores, usuarios, grupos, configuracion',
                'ruta' => route('administracion'),
                'icono' => 'admin',
                'color' => 'orange',
            ];
        }

        // Estadisticas rapidas
        $stats = [
            'totalTerritorios' => 0,
            'territoriosActivos' => 0,
            'territoriosLibres' => 0,
            'territoriosAtrasados' => 0,
            'publicadoresActivos' => 0,
        ];

        try {
            if ($congregacionId) {
                $allTerritorios = Territorio::where('congregacion_id', $congregacionId)->get();
                $stats['publicadoresActivos'] = Publicador::where('congregacion_id', $congregacionId)->where('activo', true)->count();
            } else {
                $allTerritorios = Territorio::all();
                $stats['publicadoresActivos'] = Publicador::where('activo', true)->count();
            }

            $stats['totalTerritorios'] = $allTerritorios->count();

            foreach ($allTerritorios as $territorio) {
                $estado = $territorio->calcularEstado();
                if ($estado === 'activo') $stats['territoriosActivos']++;
                elseif ($estado === 'libre') $stats['territoriosLibres']++;
                elseif ($estado === 'atrasado') $stats['territoriosAtrasados']++;
            }
        } catch (\Exception $e) {
            \Log::error('Error en DashboardController: ' . $e->getMessage());
        }

        return view('dashboard', compact('modulos', 'stats', 'congregacion'));
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
