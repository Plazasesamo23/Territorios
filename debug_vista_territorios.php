<?php
require_once 'vendor/autoload.php';

use App\Models\Territorio;
use App\Models\Registro;
use Carbon\Carbon;

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🔍 DEBUGGER: VISTA DE TERRITORIOS\n";
echo str_repeat("=", 60) . "\n";

// Simular exactamente lo que hace el controlador TerritorioController::index()
$allTerritorios = Territorio::with(['registros.publicador'])->get();

echo "📊 PRIMEROS 10 TERRITORIOS (como en la vista):\n\n";

foreach ($allTerritorios->take(10) as $territorio) {
    echo "🏠 TERRITORIO #{$territorio->numero}\n";
    echo "   🆔 ID: {$territorio->id}\n";
    echo "   📛 Nombre: " . ($territorio->nombre ?? 'Sin nombre') . "\n";
    
    // Método que usa la vista
    $estado = $territorio->calcularEstado();
    echo "   🎯 Estado calculado: {$estado}\n";
    
    // Verificar disponibilidad
    $disponible = $territorio->estaDisponibleParaAsignar();
    echo "   ✅ Disponible para asignar: " . ($disponible ? 'SÍ' : 'NO') . "\n";
    
    if (!$disponible) {
        echo "   ❌ Motivo: " . $territorio->motivoNoDisponible() . "\n";
        echo "   ⏰ Días restantes: " . $territorio->diasRestantesParaEstarDisponible() . "\n";
    }
    
    // Ver registros para debug
    $ultimoRegistro = $territorio->registros()->latest('fecha_salida')->first();
    if ($ultimoRegistro) {
        echo "   📋 Último registro:\n";
        echo "      👤 Publicador: {$ultimoRegistro->publicador->nombre_completo}\n";
        echo "      📅 Salida: {$ultimoRegistro->fecha_salida}\n";
        echo "      📅 Entrada: " . ($ultimoRegistro->fecha_entrada ?? 'NO DEVUELTO') . "\n";
        
        if ($ultimoRegistro->fecha_entrada) {
            $diasDesdeDevolucion = Carbon::parse($ultimoRegistro->fecha_entrada)->diffInDays(now());
            echo "      ⏰ Días desde devolución: {$diasDesdeDevolucion}\n";
        }
    }
    
    echo "\n" . str_repeat("-", 50) . "\n";
}

// Verificar específicamente territorios en estado "libre"
echo "\n🟢 TERRITORIOS EN ESTADO 'LIBRE':\n";
$territoriosLibres = $allTerritorios->filter(function($territorio) {
    return $territorio->calcularEstado() === 'libre';
});

echo "Total territorios libres: " . $territoriosLibres->count() . "\n\n";

foreach ($territoriosLibres->take(5) as $territorio) {
    echo "#{$territorio->numero} - Estado: {$territorio->calcularEstado()} - ";
    echo "Disponible: " . ($territorio->estaDisponibleParaAsignar() ? 'SÍ' : 'NO') . "\n";
    
    if (!$territorio->estaDisponibleParaAsignar()) {
        echo "   ❌ Motivo: " . $territorio->motivoNoDisponible() . "\n";
    }
}

echo "\n✅ Debug completado\n";

