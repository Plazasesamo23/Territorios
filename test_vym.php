<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$congregacionId = 1;
$resultados = [];

$autorizaciones = App\Models\ReunionAutorizacion::where('congregacion_id', $congregacionId)
    ->selectRaw('tipo_parte, count(*) as total')
    ->groupBy('tipo_parte')
    ->pluck('total', 'tipo_parte')
    ->toArray();
$resultados[] = "Autorizaciones: " . json_encode($autorizaciones);

$programas = App\Models\ReunionPrograma::where('congregacion_id', $congregacionId)->count();
$resultados[] = "Programas: {$programas}";

$anciano = App\Models\Publicador::where('congregacion_id', $congregacionId)
    ->where('activo', true)->whereNotNull('genero')->where('es_anciano', true)->first();
if ($anciano) {
    $tipos = ['presidente','oracion_inicio','discurso_tesoros','perlas','lectura',
              'empiece_conversaciones','discurso_vida','conductor_estudio','lector_estudio','ayudante'];
    $res = [];
    foreach ($tipos as $t) {
        $res[$t] = $anciano->puedeHacerParte($t) ? 'SI' : 'NO';
    }
    $resultados[] = "puedeHacerParte({$anciano->nombre_completo}, anciano): " . json_encode($res);
}

$hermana = App\Models\Publicador::where('congregacion_id', $congregacionId)
    ->where('activo', true)->where('genero', 'F')->where('excluido_reuniones', false)->first();
if ($hermana) {
    $tipos = ['presidente','oracion_inicio','lectura','empiece_conversaciones','ayudante'];
    $res = [];
    foreach ($tipos as $t) {
        $res[$t] = $hermana->puedeHacerParte($t) ? 'SI' : 'NO';
    }
    $resultados[] = "puedeHacerParte({$hermana->nombre_completo}, hermana): " . json_encode($res);
}

$sinGenero = App\Models\Publicador::where('congregacion_id', $congregacionId)
    ->where('activo', true)->whereNull('genero')->count();
$resultados[] = "Sin genero: {$sinGenero}";

$excluidos = App\Models\Publicador::where('congregacion_id', $congregacionId)
    ->where('excluido_reuniones', true)->count();
$resultados[] = "Excluidos: {$excluidos}";

$pool = App\Models\Publicador::where('congregacion_id', $congregacionId)
    ->where('activo', true)->whereNotNull('genero')->where('excluido_reuniones', false)->count();
$resultados[] = "Pool publicadores: {$pool}";

try {
    $service = new App\Services\AsignacionReunionService($congregacionId);
    $resultados[] = "AsignacionReunionService: OK";
} catch (Exception $e) {
    $resultados[] = "AsignacionReunionService ERROR: " . $e->getMessage();
}

$auto2 = App\Models\ReunionAutorizacion::where('congregacion_id', 2)->count();
$resultados[] = "Autorizaciones cong 2: {$auto2}";

echo "\n=== TEST VYM ===\n";
foreach ($resultados as $r) echo "  {$r}\n";
echo "\n";
