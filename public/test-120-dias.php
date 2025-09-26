<?php
// Test para verificar configuración de 120 días
require_once '../vendor/autoload.php';

// Cargar Laravel
$app = require_once '../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "<h1>TEST: Configuración de 120 días</h1>";

echo "<h2>1. Valor de configuración:</h2>";
$diasLimite = config('territorios.dias_limite_activo', 'NO ENCONTRADO');
echo "<p><strong>dias_limite_activo:</strong> " . $diasLimite . "</p>";

echo "<h2>2. Territorio #1 - Estado actual:</h2>";
$territorio = App\Models\Territorio::find(1);
if ($territorio) {
    $estado = $territorio->calcularEstado();
    echo "<p><strong>Estado:</strong> " . $estado . "</p>";
    
    // Obtener último registro
    $ultimoRegistro = $territorio->registros()->latest('fecha_salida')->first();
    if ($ultimoRegistro && !$ultimoRegistro->fecha_entrada) {
        $fechaSalida = \Carbon\Carbon::parse($ultimoRegistro->fecha_salida);
        $diasAsignado = $fechaSalida->diffInDays(now());
        echo "<p><strong>Días asignado:</strong> " . $diasAsignado . "</p>";
        echo "<p><strong>Fecha salida:</strong> " . $fechaSalida->format('Y-m-d') . "</p>";
        echo "<p><strong>Debe ser atrasado después de:</strong> " . $diasLimite . " días</p>";
        echo "<p><strong>¿Es atrasado?:</strong> " . ($diasAsignado > $diasLimite ? 'SÍ' : 'NO') . "</p>";
    }
} else {
    echo "<p>Territorio #1 no encontrado</p>";
}

echo "<h2>3. Test directo con 120 días:</h2>";
if ($ultimoRegistro && !$ultimoRegistro->fecha_entrada) {
    $fechaSalida = \Carbon\Carbon::parse($ultimoRegistro->fecha_salida);
    $diasAsignado = $fechaSalida->diffInDays(now());
    echo "<p><strong>¿Sería atrasado con 120 días?:</strong> " . ($diasAsignado > 120 ? 'SÍ' : 'NO') . "</p>";
    echo "<p><strong>¿Sería atrasado con 80 días?:</strong> " . ($diasAsignado > 80 ? 'SÍ' : 'NO') . "</p>";
}
?>


