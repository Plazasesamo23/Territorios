<?php
require_once '../vendor/autoload.php';
require_once '../bootstrap/app.php';

use App\Models\Territorio;
use App\Models\Registro;
use Carbon\Carbon;

echo "<h2>🔍 INVESTIGACIÓN: Territorios sin datos en S13</h2>";

// Territorios a investigar
$territoriosInvestigar = [76, 85, 1, 21]; // Incluyo 1 y 21 como controles

$añoServicio = 2025;
$añoSiguiente = 2026;
$fechaInicioAñoServicio = Carbon::create($añoServicio, 9, 1);
$fechaFinAñoServicio = Carbon::create($añoSiguiente, 8, 31);

foreach($territoriosInvestigar as $num) {
    echo "<div style='border: 1px solid #ccc; margin: 10px; padding: 10px;'>";
    echo "<h3>🗺️ Territorio #{$num}</h3>";
    
    $territorio = Territorio::where('numero', $num)->first();
    
    if(!$territorio) {
        echo "❌ <strong>ERROR: Territorio no existe en la base de datos</strong><br>";
        continue;
    }
    
    echo "✅ Territorio existe en BD<br>";
    
    // Todos los registros del territorio
    $todosRegistros = $territorio->registros()->with('publicador')->orderBy('fecha_salida', 'desc')->get();
    echo "📊 <strong>Total registros históricos:</strong> " . $todosRegistros->count() . "<br>";
    
    if($todosRegistros->count() > 0) {
        echo "<strong>Últimos 3 registros:</strong><br>";
        foreach($todosRegistros->take(3) as $reg) {
            echo "- {$reg->publicador->nombre} {$reg->publicador->apellidos}: {$reg->fecha_salida->format('d/m/Y')} → ";
            echo ($reg->fecha_entrada ? $reg->fecha_entrada->format('d/m/Y') : 'En curso') . "<br>";
        }
    }
    
    echo "<hr>";
    
    // Lógica del S13 (igual que el controlador)
    
    // 1. Registros activos del año anterior
    $registrosAñoAnterior = $territorio->registros()
        ->where('fecha_salida', '<', $fechaInicioAñoServicio)
        ->whereNull('fecha_entrada')
        ->with('publicador')
        ->orderBy('fecha_salida', 'desc')
        ->take(2)
        ->get();
    
    echo "📅 <strong>Registros activos año anterior (sin fecha_entrada):</strong> " . $registrosAñoAnterior->count() . "<br>";
    foreach($registrosAñoAnterior as $reg) {
        echo "- {$reg->publicador->nombre}: {$reg->fecha_salida->format('d/m/Y')} (activo)<br>";
    }
    
    // 2. Registros completados en últimos 6 meses
    $registrosRecientes = $territorio->registros()
        ->where('fecha_salida', '>=', Carbon::create($añoServicio, 3, 1))
        ->where('fecha_salida', '<', $fechaInicioAñoServicio)
        ->whereNotNull('fecha_entrada')
        ->with('publicador')
        ->orderBy('fecha_salida', 'desc')
        ->take(1)
        ->get();
    
    echo "📅 <strong>Registros recientes completados (6 meses):</strong> " . $registrosRecientes->count() . "<br>";
    foreach($registrosRecientes as $reg) {
        echo "- {$reg->publicador->nombre}: {$reg->fecha_salida->format('d/m/Y')} → {$reg->fecha_entrada->format('d/m/Y')}<br>";
    }
    
    // 3. Registros del año actual
    $registrosAñoActual = $territorio->registros()
        ->whereBetween('fecha_salida', [$fechaInicioAñoServicio, $fechaFinAñoServicio])
        ->with('publicador')
        ->orderBy('fecha_salida')
        ->get();
    
    echo "📅 <strong>Registros año servicio actual ({$añoServicio}-{$añoSiguiente}):</strong> " . $registrosAñoActual->count() . "<br>";
    foreach($registrosAñoActual as $reg) {
        echo "- {$reg->publicador->nombre}: {$reg->fecha_salida->format('d/m/Y')} → ";
        echo ($reg->fecha_entrada ? $reg->fecha_entrada->format('d/m/Y') : 'En curso') . "<br>";
    }
    
    // Total para S13
    $totalParaS13 = $registrosRecientes->count() + $registrosAñoAnterior->count() + $registrosAñoActual->count();
    echo "<strong>🎯 TOTAL PARA S13:</strong> " . $totalParaS13 . "<br>";
    
    if($totalParaS13 == 0) {
        echo "❌ <strong>RAZÓN: Sin registros en el período del año de servicio ni continuidad del año anterior</strong><br>";
    }
    
    echo "</div>";
}

echo "<hr>";
echo "<h3>📋 POSIBLES CAUSAS:</h3>";
echo "1. <strong>Sin registros en período:</strong> El territorio no fue trabajado en el año de servicio actual<br>";
echo "2. <strong>Sin continuidad:</strong> No hay registros activos del año anterior<br>";
echo "3. <strong>Registros muy antiguos:</strong> Último trabajo hace más de 6 meses antes del año de servicio<br>";
echo "4. <strong>Datos incorrectos:</strong> Fechas mal capturadas en la importación<br>";
?>
