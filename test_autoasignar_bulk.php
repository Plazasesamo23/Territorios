<?php
/**
 * Crear 24 semanas + auto-asignar usando los servicios reales de la app
 * Luego generar informe de distribucion
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle($request = Illuminate\Http\Request::capture());

use App\Models\ReunionPrograma;
use App\Models\ReunionHistorial;
use App\Models\Publicador;
use App\Services\AsignacionReunionService;
use App\Services\ImportadorVymService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

$congregacionId = 1;
$cantidad = 24;

// Simular sesion para el scope de congregacion
session(['congregacion_activa_id' => $congregacionId]);

echo "=== CREACION DE {$cantidad} SEMANAS + AUTO-ASIGNACION ===\n\n";

// 1. Fecha inicio
// Empezar desde esta semana (o la primera semana sin programa)
$fechaInicio = Carbon::now()->startOfWeek(Carbon::MONDAY);

echo "Desde: {$fechaInicio->format('d/m/Y')}\n\n";

// 2. Crear programas (mismo que ReunionController::store)
$importador = new ImportadorVymService();
$creados = 0;
$importados = 0;
$programasIds = [];

for ($i = 0; $i < $cantidad; $i++) {
    $fecha = $fechaInicio->copy()->addWeeks($i);

    $existente = DB::table('reuniones_programas')
        ->where('congregacion_id', $congregacionId)
        ->where('fecha_semana', $fecha->format('Y-m-d'))->first();

    if ($existente) {
        $programasIds[] = $existente->id;
        echo "  [--] {$fecha->format('d/m/Y')} ya existe (ID {$existente->id})\n";
        continue;
    }

    $programa = ReunionPrograma::create([
        'congregacion_id' => $congregacionId,
        'fecha_semana' => $fecha->format('Y-m-d'),
        'estado' => 'borrador',
    ]);

    $resultado = $importador->importar($programa);
    if (!empty($resultado['success'])) {
        $importados++;
        echo "  [OK] {$fecha->format('d/m/Y')} importado jw.org\n";
    } else {
        AsignacionReunionService::generarPartesEstandar($programa);
        echo "  [FB] {$fecha->format('d/m/Y')} partes estandar\n";
    }
    $programasIds[] = $programa->id;
    $creados++;
}

echo "\nCreados: {$creados} | Importados jw.org: {$importados}\n";

// 3. Auto-asignar (mismo que ReunionController::autoAsignar)
echo "\n=== AUTO-ASIGNACION ===\n\n";

$totalAsignados = 0;

foreach ($programasIds as $progId) {
    $programa = ReunionPrograma::with('partes')->find($progId);
    $service = new AsignacionReunionService($congregacionId);
    $resultado = $service->autoAsignar($programa);

    $totalAsignados += $resultado['asignados'];
    $info = "{$programa->fecha_semana->format('d/m/Y')}: {$resultado['asignados']} asig.";
    if (!empty($resultado['sin_candidatos'])) {
        $info .= " | SIN CANDIDATOS: " . implode(', ', $resultado['sin_candidatos']);
    }
    echo "  {$info}\n";
}

echo "\nTotal asignaciones realizadas: {$totalAsignados}\n";

// 4. INFORME DE DISTRIBUCION
echo "\n=== DISTRIBUCION ===\n\n";

$historial = ReunionHistorial::where('congregacion_id', $congregacionId)
    ->whereIn('programa_id', $programasIds)->get();

$porPub = $historial->groupBy('publicador_id');
$pubs = Publicador::where('congregacion_id', $congregacionId)
    ->where('activo', true)->get()->keyBy('id');

$datos = [];
foreach ($porPub as $pubId => $regs) {
    $pub = $pubs->get($pubId);
    if (!$pub) continue;
    $nomb = $pub->es_anciano ? 'Anciano' : ($pub->es_siervo_ministerial ? 'SM' : 'Pub');
    $datos[] = [
        'nombre' => $pub->nombre_completo,
        'nomb' => $nomb,
        'total' => $regs->count(),
        'semanas' => $regs->pluck('fecha_semana')->unique()->count(),
        'tipos' => $regs->groupBy('tipo_parte')->map->count()->toArray(),
    ];
}
usort($datos, fn($a, $b) => $b['total'] - $a['total']);

$totales = array_column($datos, 'total');
$prom = count($totales) > 0 ? round(array_sum($totales) / count($totales), 1) : 0;
$maxV = max($totales ?: [0]);
$minV = min($totales ?: [0]);

echo "Personas con asignaciones: " . count($datos) . "\n";
echo "Promedio: {$prom} | Max: {$maxV} | Min: {$minV} | Rango: " . ($maxV - $minV) . "\n\n";

echo "--- POR NOMBRAMIENTO ---\n";
foreach (['Anciano', 'SM', 'Pub'] as $n) {
    $g = array_filter($datos, fn($d) => $d['nomb'] === $n);
    if (empty($g)) continue;
    $t = array_column($g, 'total');
    echo "  {$n}: " . count($g) . " personas, prom " . round(array_sum($t)/count($t), 1) . " asig.\n";
}

echo "\n--- DETALLE ---\n";
echo str_pad('Nombre', 32) . str_pad('Tipo', 6) . str_pad('Tot', 5) . str_pad('Sem', 5) . "Desglose\n";
echo str_repeat('-', 85) . "\n";

foreach ($datos as $d) {
    $tipos = '';
    foreach ($d['tipos'] as $tp => $c) {
        $short = match($tp) {
            'presidente' => 'pres',
            'oracion_inicio' => 'oraI',
            'oracion_final' => 'oraF',
            'discurso_tesoros' => 'dTes',
            'perlas' => 'perl',
            'lectura' => 'lect',
            'empiece_conversaciones' => 'empC',
            'haga_revisitas' => 'hRev',
            'haga_discipulos' => 'hDis',
            'explique_creencias' => 'expC',
            'discurso_maestros' => 'dMae',
            'discurso_vida' => 'dVid',
            'ayudante' => 'ayud',
            'conductor_estudio' => 'cond',
            'lector_estudio' => 'lect_e',
            default => substr($tp, 0, 4),
        };
        $tipos .= "{$short}:{$c} ";
    }
    echo str_pad(mb_substr($d['nombre'], 0, 31), 32)
        . str_pad($d['nomb'], 6)
        . str_pad($d['total'], 5)
        . str_pad($d['semanas'], 5)
        . $tipos . "\n";
}

// Alertas
echo "\n--- ALERTAS ---\n";
$umbral = $prom * 1.6;
$found = false;
foreach ($datos as $d) {
    if ($d['total'] > $umbral) {
        echo "  SOBRECARGA: {$d['nombre']} = {$d['total']} (prom {$prom})\n";
        $found = true;
    }
}
$sinAsig = $pubs->filter(fn($p) => !$p->excluido_reuniones && !$porPub->has($p->id));
if ($sinAsig->count() > 0) {
    echo "  SIN ASIGNACIONES: " . $sinAsig->pluck('nombre_completo')->implode(', ') . "\n";
    $found = true;
}
if (!$found) echo "  Todo equilibrado.\n";

echo "\n=== FIN ===\n";
