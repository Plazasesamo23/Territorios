<?php
// Debug directo para ver en navegador
require_once '../vendor/autoload.php';

use App\Models\Territorio;
use Carbon\Carbon;

// Configurar Laravel
$app = require_once '../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

?>
<!DOCTYPE html>
<html>
<head>
    <title>Debug Territorio #1</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .result { background: #f0f0f0; padding: 10px; margin: 10px 0; border-radius: 5px; }
        .error { background: #ffcccb; }
        .success { background: #d4edda; }
    </style>
</head>
<body>
    <h1>🔍 DEBUG TERRITORIO #1 - En Navegador</h1>
    <p>Fecha actual: <?= now()->format('d/m/Y H:i:s') ?></p>

    <?php
    try {
        // Buscar territorio #1
        $territorio = Territorio::where('numero', 1)->first();
        
        if (!$territorio) {
            echo '<div class="result error">❌ No se encontró el territorio #1</div>';
            exit;
        }
        
        echo '<div class="result success">✅ Territorio #1 encontrado (ID: ' . $territorio->id . ')</div>';
        
        // Probar métodos
        $estado = $territorio->calcularEstado();
        $disponible = $territorio->estaDisponibleParaAsignar();
        $diasRestantes = $territorio->diasRestantesParaEstarDisponible();
        $motivo = $territorio->motivoNoDisponible();
        
        echo '<h2>📊 Resultados:</h2>';
        echo '<div class="result">🎯 Estado calculado: <strong>' . $estado . '</strong></div>';
        echo '<div class="result">✅ Disponible para asignar: <strong>' . ($disponible ? 'SÍ' : 'NO') . '</strong></div>';
        echo '<div class="result">⏰ Días restantes: <strong>' . $diasRestantes . '</strong></div>';
        echo '<div class="result">❌ Motivo: <strong>' . $motivo . '</strong></div>';
        
        // Ver último registro devuelto
        $ultimoRegistroDevuelto = $territorio->registros()
            ->whereNotNull('fecha_entrada')
            ->latest('fecha_entrada')
            ->first();
            
        if ($ultimoRegistroDevuelto) {
            $diasDesdeDevolucion = Carbon::parse($ultimoRegistroDevuelto->fecha_entrada)->diffInDays(now());
            echo '<h2>📋 Último registro devuelto:</h2>';
            echo '<div class="result">👤 Publicador: ' . $ultimoRegistroDevuelto->publicador->nombre_completo . '</div>';
            echo '<div class="result">📅 Fecha devolución: ' . $ultimoRegistroDevuelto->fecha_entrada . '</div>';
            echo '<div class="result">⏰ Días desde devolución: ' . round($diasDesdeDevolucion, 2) . '</div>';
            echo '<div class="result">🚫 Debe esperar 90 días</div>';
            echo '<div class="result">📊 Días que faltan: ' . round(90 - $diasDesdeDevolucion, 2) . '</div>';
        }
        
        // Test directo del código en calcularEstado
        echo '<h2>🧪 Test directo del código:</h2>';
        $ultimoRegistro = $territorio->registros()->latest('fecha_salida')->first();
        if ($ultimoRegistro && $ultimoRegistro->fecha_entrada) {
            $fechaDevolucion = Carbon::parse($ultimoRegistro->fecha_entrada);
            $diasDesdeDevolucion = $fechaDevolucion->diffInDays(now());
            
            echo '<div class="result">📅 Último registro tiene fecha_entrada: SÍ</div>';
            echo '<div class="result">⏰ Días desde devolución: ' . round($diasDesdeDevolucion, 2) . '</div>';
            echo '<div class="result">🔢 Comparación: ' . round($diasDesdeDevolucion, 2) . ' < 90 = ' . ($diasDesdeDevolucion < 90 ? 'TRUE' : 'FALSE') . '</div>';
            
            if ($diasDesdeDevolucion < 90) {
                echo '<div class="result success">✅ Debería retornar "archivo"</div>';
            } else {
                echo '<div class="result error">❌ Debería retornar "libre"</div>';
            }
        }
        
    } catch (Exception $e) {
        echo '<div class="result error">❌ Error: ' . $e->getMessage() . '</div>';
    }
    ?>
    
    <h2>🌐 URLs para probar:</h2>
    <ul>
        <li><a href="/territorios/public/territorios">Ver todos los territorios</a></li>
        <li><a href="/territorios/public/territorios?estado=archivo">Ver territorios en archivo</a></li>
        <li><a href="/territorios/public/registros/create">Crear nueva asignación</a></li>
    </ul>
</body>
</html>

