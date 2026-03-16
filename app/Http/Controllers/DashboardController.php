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
        // Módulos disponibles para el dashboard
        $modulos = [
            [
                'nombre' => 'Territorios',
                'descripcion' => 'Gestionar territorios, asignaciones y estados',
                'ruta' => route('territorios.index'),
                'icono' => 'territorios',
                'color' => 'green',
            ],
            [
                'nombre' => 'Registros',
                'descripcion' => 'Asignaciones activas, historial y archivados',
                'ruta' => route('registros.index'),
                'icono' => 'registros',
                'color' => 'blue',
            ],
            [
                'nombre' => 'S-13',
                'descripcion' => 'Reportes S-13, PDF y vista previa',
                'ruta' => route('s13.index'),
                'icono' => 's13',
                'color' => 'purple',
            ],
            [
                'nombre' => 'Administracion',
                'descripcion' => 'Publicadores, configuracion del sistema',
                'ruta' => route('publicadores.index'),
                'icono' => 'admin',
                'color' => 'orange',
            ],
        ];

        // Estadísticas rápidas
        $stats = [
            'totalTerritorios' => 0,
            'territoriosActivos' => 0,
            'territoriosLibres' => 0,
            'territoriosAtrasados' => 0,
        ];

        try {
            $allTerritorios = Territorio::all();
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

        return view('dashboard', compact('modulos', 'stats'));
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
