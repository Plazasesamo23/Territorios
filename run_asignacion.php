<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Simular sesion de congregacion
session(['congregacion_activa_id' => 1]);

use App\Models\TurnoGenerado;
use App\Services\AsignacionPpocService;

$turnos = TurnoGenerado::where('congregacion_id', 1)
    ->whereYear('fecha', 2026)
    ->whereMonth('fecha', 1)
    ->where('estado', '!=', 'cancelado')
    ->count();

echo "Turnos encontrados: " . $turnos . "\n";

$service = new AsignacionPpocService(1);
$resultado = $service->generarMes(2026, 1);

echo "Turnos asignados: " . $resultado['asignados'] . "\n";
echo "Turnos incompletos: " . $resultado['incompletos'] . "\n";

if (!empty($resultado['errores'])) {
    echo "Errores: " . implode(', ', $resultado['errores']) . "\n";
}
