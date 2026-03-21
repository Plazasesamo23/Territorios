<?php
// Test script para verificar el módulo Reuniones VyM
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Boot the app
$app->boot();

$congregacionId = 1;
$errores = [];
$ok = [];

// 1. Test modelo ReunionAutorizacion existe
try {
    $count = App\Models\ReunionAutorizacion::where('congregacion_id', $congregacionId)->count();
    $ok[] = "ReunionAutorizacion: {$count} registros para congregacion {$congregacionId}";
} catch (\Exception $e) {
    $errores[] = "ReunionAutorizacion ERROR: " . $e->getMessage();
}

// 2. Test puedeHacerParte con tabla autorizaciones
try {
    $pub = App\Models\Publicador::where('congregacion_id', $congregacionId)
        ->where('activo', true)
        ->whereNotNull('genero')
        ->first();
    if ($pub) {
        $tipos = ['presidente','oracion_inicio','oracion_final','discurso_tesoros','perlas','lectura',
                  'empiece_conversaciones','haga_revisitas','discurso_vida','conductor_estudio','lector_estudio','ayudante'];
        $resultados = [];
        foreach ($tipos as $t) {
            $resultados[$t] = $pub->puedeHacerParte($t) ? 'SI' : 'NO';
        }
        $ok[] = "puedeHacerParte({$pub->nombre_completo}): " . json_encode($resultados);
    }
} catch (\Exception $e) {
    $errores[] = "puedeHacerParte ERROR: " . $e->getMessage();
}

// 3. Test programas existen
try {
    $programas = App\Models\ReunionPrograma::where('congregacion_id', $congregacionId)->count();
    $ok[] = "Programas VyM: {$programas} para congregacion {$congregacionId}";
} catch (\Exception $e) {
    $errores[] = "Programas ERROR: " . $e->getMessage();
}

// 4. Test autorizaciones por tipo
try {
    $porTipo = App\Models\ReunionAutorizacion::where('congregacion_id', $congregacionId)
        ->selectRaw('tipo_parte, count(*) as total')
        ->groupBy('tipo_parte')
        ->pluck('total', 'tipo_parte')
        ->toArray();
    $ok[] = "Autorizaciones por tipo: " . json_encode($porTipo);
} catch (\Exception $e) {
    $errores[] = "Autorizaciones por tipo ERROR: " . $e->getMessage();
}

// 5. Test auto-asignar servicio
try {
    $programa = App\Models\ReunionPrograma::where('congregacion_id', $congregacionId)
        ->orderByDesc('fecha_semana')
        ->first();
    if ($programa) {
        $service = new App\Services\AsignacionReunionService($congregacionId);
        $ok[] = "AsignacionReunionService instanciado OK para programa {$programa->fecha_semana}";
    }
} catch (\Exception $e) {
    $errores[] = "AsignacionReunionService ERROR: " . $e->getMessage();
}

// 6. Test publicadores excluidos
try {
    $excluidos = App\Models\Publicador::where('congregacion_id', $congregacionId)
        ->where('excluido_reuniones', true)->count();
    $ok[] = "Publicadores excluidos: {$excluidos}";
} catch (\Exception $e) {
    $errores[] = "Excluidos ERROR: " . $e->getMessage();
}

// 7. Test vista autorizaciones data
try {
    $activos = App\Models\Publicador::where('congregacion_id', $congregacionId)
        ->where('activo', true)
        ->whereNotNull('genero')
        ->where('excluido_reuniones', false)
        ->count();
    $ok[] = "Publicadores activos con genero (pool): {$activos}";
} catch (\Exception $e) {
    $errores[] = "Pool ERROR: " . $e->getMessage();
}

echo "\n=== RESULTADOS TEST REUNIONES VyM ===\n\n";
echo "OK (" . count($ok) . "):\n";
foreach ($ok as $m) echo "  ✓ {$m}\n";
if ($errores) {
    echo "\nERRORES (" . count($errores) . "):\n";
    foreach ($errores as $m) echo "  ✗ {$m}\n";
}
echo "\n";
