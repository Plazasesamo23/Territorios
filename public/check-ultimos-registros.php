<?php
require_once '../vendor/autoload.php';
require_once '../bootstrap/app.php';

use App\Models\Territorio;
use App\Models\Registro;

echo "<h2>🔍 ÚLTIMOS REGISTROS DE TERRITORIOS 76 Y 85</h2>";

$territorios = [76, 85];

foreach($territorios as $num) {
    echo "<div style='border: 2px solid #333; margin: 15px; padding: 15px; background: #f9f9f9;'>";
    echo "<h3>🗺️ Territorio #{$num}</h3>";
    
    $territorio = Territorio::where('numero', $num)->first();
    
    if(!$territorio) {
        echo "❌ <strong>Territorio no existe</strong><br>";
        continue;
    }
    
    // Obtener TODOS los registros del territorio, ordenados por fecha más reciente
    $registros = $territorio->registros()
        ->with('publicador')
        ->orderBy('fecha_salida', 'desc')
        ->get();
    
    echo "📊 <strong>Total de registros históricos:</strong> " . $registros->count() . "<br><br>";
    
    if($registros->count() > 0) {
        echo "<strong>📅 ÚLTIMO REGISTRO:</strong><br>";
        $ultimo = $registros->first();
        echo "👤 <strong>Publicador:</strong> {$ultimo->publicador->nombre} {$ultimo->publicador->apellidos}<br>";
        echo "📅 <strong>Fecha de salida:</strong> {$ultimo->fecha_salida->format('d/m/Y')}<br>";
        echo "📅 <strong>Fecha de entrada:</strong> " . ($ultimo->fecha_entrada ? $ultimo->fecha_entrada->format('d/m/Y') : 'Sin devolver (ACTIVO)') . "<br>";
        echo "⏰ <strong>Hace cuánto:</strong> " . $ultimo->fecha_salida->diffForHumans() . "<br>";
        
        if($ultimo->fecha_entrada) {
            echo "⏱️ <strong>Duración:</strong> " . $ultimo->fecha_salida->diffInDays($ultimo->fecha_entrada) . " días<br>";
        }
        
        echo "<hr>";
        
        if($registros->count() > 1) {
            echo "<strong>📋 TODOS LOS REGISTROS (más recientes primero):</strong><br>";
            foreach($registros as $index => $reg) {
                $estado = $reg->fecha_entrada ? "Completado" : "ACTIVO";
                echo ($index + 1) . ". {$reg->publicador->nombre} {$reg->publicador->apellidos} - ";
                echo "{$reg->fecha_salida->format('d/m/Y')} → ";
                echo ($reg->fecha_entrada ? $reg->fecha_entrada->format('d/m/Y') : 'En curso') . " ";
                echo "({$estado})<br>";
            }
        }
    } else {
        echo "❌ <strong>NO HAY REGISTROS PARA ESTE TERRITORIO</strong><br>";
        echo "💡 Esto significa que nunca se ha asignado este territorio o los datos no se importaron correctamente.<br>";
    }
    
    echo "</div>";
}

// Verificar estadísticas generales
echo "<div style='border: 2px solid #0066cc; margin: 15px; padding: 15px; background: #e6f3ff;'>";
echo "<h3>📊 ESTADÍSTICAS GENERALES</h3>";
echo "🗺️ <strong>Total territorios en BD:</strong> " . Territorio::count() . "<br>";
echo "📝 <strong>Total registros en BD:</strong> " . Registro::count() . "<br>";
echo "🗺️ <strong>Territorios con registros:</strong> " . Territorio::has('registros')->count() . "<br>";
echo "🗺️ <strong>Territorios SIN registros:</strong> " . Territorio::doesntHave('registros')->count() . "<br>";

$territoriosSinRegistros = Territorio::doesntHave('registros')->pluck('numero')->toArray();
if(count($territoriosSinRegistros) > 0) {
    echo "<strong>📋 Territorios sin registros:</strong> " . implode(', ', $territoriosSinRegistros) . "<br>";
}
echo "</div>";
?>
