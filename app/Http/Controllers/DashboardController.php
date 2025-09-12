<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Territorio;
use App\Models\Publicador;
use App\Models\Registro;

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
     * Guardar configuración del sistema
     */
    public function guardarConfiguracion(Request $request)
    {
        $request->validate([
            'dias_limite_activo' => 'required|integer|min:1|max:365',
            'dias_archivo' => 'required|integer|min:1|max:365'
        ]);

        // Actualizar el archivo de configuración
        $configPath = config_path('territorios.php');
        $configContent = file_get_contents($configPath);

        // Reemplazar los valores en el archivo
        $configContent = preg_replace(
            "/'dias_limite_activo' => env\('TERRITORIOS_DIAS_LIMITE_ACTIVO', \d+\)/",
            "'dias_limite_activo' => env('TERRITORIOS_DIAS_LIMITE_ACTIVO', {$request->dias_limite_activo})",
            $configContent
        );

        $configContent = preg_replace(
            "/'dias_archivo' => env\('TERRITORIOS_DIAS_ARCHIVO', \d+\)/",
            "'dias_archivo' => env('TERRITORIOS_DIAS_ARCHIVO', {$request->dias_archivo})",
            $configContent
        );

        file_put_contents($configPath, $configContent);

        // Limpiar cache de configuración
        \Artisan::call('config:clear');

        return redirect()->route('configuracion')
            ->with('success', 'Configuración actualizada correctamente.');
    }
}
