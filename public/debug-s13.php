<?php
require_once '../vendor/autoload.php';
require_once '../bootstrap/app.php';

use App\Models\Territorio;
use Carbon\Carbon;

// Probar territorio 21, 22, 23
$territorios = [21, 22, 23];

foreach($territorios as $num) {
    $territorio = Territorio::where('numero', $num)->first();
    
    if($territorio) {
        echo "<h3>Territorio #{$num}</h3>";
        
        // Lógica igual que el controlador
        $añoServicio = 2025;
        $añoSiguiente = 2026;
        $fechaInicioAñoServicio = Carbon::create($añoServicio, 9, 1);
        $fechaFinAñoServicio = Carbon::create($añoSiguiente, 8, 31);
        
        // Registros activos del año anterior
        $registrosAñoAnterior = $territorio->registros()
            ->where('fecha_salida', '<', $fechaInicioAñoServicio)
            ->whereNull('fecha_entrada')
            ->with('publicador')
            ->orderBy('fecha_salida', 'desc')
            ->take(2)
            ->get();
        
        // Registros recientes completados
        $registrosRecientes = $territorio->registros()
            ->where('fecha_salida', '>=', Carbon::create($añoServicio, 3, 1))
            ->where('fecha_salida', '<', $fechaInicioAñoServicio)
            ->whereNotNull('fecha_entrada')
            ->with('publicador')
            ->orderBy('fecha_salida', 'desc')
            ->take(1)
            ->get();
        
        // Registros del año actual
        $registrosAñoActual = $territorio->registros()
            ->whereBetween('fecha_salida', [$fechaInicioAñoServicio, $fechaFinAñoServicio])
            ->with('publicador')
            ->orderBy('fecha_salida')
            ->get();
        
        // Combinar
        $todosLosRegistros = $registrosRecientes
            ->concat($registrosAñoAnterior)
            ->concat($registrosAñoActual)
            ->sortBy('fecha_salida')
            ->take(4)
            ->values();
        
        echo "<p>Total registros encontrados: " . $todosLosRegistros->count() . "</p>";
        
        foreach($todosLosRegistros as $i => $registro) {
            echo "<p>Columna {$i}: {$registro->publicador->nombre} {$registro->publicador->apellidos} - {$registro->fecha_salida->format('d-m-y')}</p>";
        }
        
        echo "<hr>";
    }
}
?>
