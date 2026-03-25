<?php
/**
 * Limpiar asignaciones de todas las semanas y re-asignar con el nuevo scoring
 * Usa los mismos servicios que la app
 */
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use App\Models\ReunionPrograma;
use App\Models\ReunionParte;
use App\Models\ReunionHistorial;
use App\Models\Publicador;
use App\Services\AsignacionReunionService;
use Illuminate\Support\Facades\DB;

$congregacionId = 1;
session(['congregacion_activa_id' => $congregacionId]);

$programas = DB::table('reuniones_programas')
    ->where('congregacion_id', $congregacionId)
    ->orderBy('fecha_semana')
    ->get();

echo "=== RE-ASIGNACION COMPLETA ({$programas->count()} semanas) ===\n\n";

// 1. Limpiar TODAS las asignaciones existentes
echo "Limpiando asignaciones...\n";
foreach ($programas as $prog) {
    DB::table('reuniones_programas')->where('id', $prog->id)->update([
        'presidente_id' => null,
        'oracion_inicio_id' => null,
        'oracion_final_id' => null,
        'conductor_estudio_id' => null,
        'lector_estudio_id' => null,
    ]);
    DB::table('reuniones_partes')->where('programa_id', $prog->id)->update([
        'publicador_id' => null,
        'ayudante_id' => null,
    ]);
}
DB::table('reuniones_historial')->where('congregacion_id', $congregacionId)->delete();
echo "Hecho.\n\n";

// 2. Auto-asignar semana por semana (mismo orden cronologico)
echo "=== AUTO-ASIGNANDO ===\n\n";
$totalAsignados = 0;

foreach ($programas as $prog) {
    $programa = ReunionPrograma::with('partes')->find($prog->id);
    $service = new AsignacionReunionService($congregacionId);
    $resultado = $service->autoAsignar($programa);

    $totalAsignados += $resultado['asignados'];
    $info = "{$programa->fecha_semana->format('d/m/Y')}: {$resultado['asignados']} asig.";
    if (!empty($resultado['sin_candidatos'])) {
        $info .= " | SIN: " . implode(', ', $resultado['sin_candidatos']);
    }
    echo "  {$info}\n";
}

echo "\nTotal: {$totalAsignados}\n";

// 3. Informe de distribucion
echo "\n=== DISTRIBUCION ===\n\n";

$historial = ReunionHistorial::where('congregacion_id', $congregacionId)->get();
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

echo "Personas: " . count($datos) . " | Prom: {$prom} | Max: {$maxV} | Min: {$minV} | Rango: " . ($maxV - $minV) . "\n\n";

echo "--- POR NOMBRAMIENTO ---\n";
foreach (['Anciano', 'SM', 'Pub'] as $n) {
    $g = array_filter($datos, fn($d) => $d['nomb'] === $n);
    if (empty($g)) continue;
    $t = array_column($g, 'total');
    echo "  {$n}: " . count($g) . " pers, prom " . round(array_sum($t)/count($t), 1) . " asig. (min " . min($t) . ", max " . max($t) . ")\n";
}

echo "\n--- TOP 30 ---\n";
echo str_pad('Nombre', 32) . str_pad('Tipo', 8) . str_pad('Tot', 5) . "Desglose\n";
echo str_repeat('-', 90) . "\n";

$top30 = array_slice($datos, 0, 30);
foreach ($top30 as $d) {
    $tipos = '';
    foreach ($d['tipos'] as $tp => $c) {
        $short = match($tp) {
            'presidente' => 'pres', 'oracion_inicio' => 'oraI', 'oracion_final' => 'oraF',
            'discurso_tesoros' => 'dTes', 'perlas' => 'perl', 'lectura' => 'lect',
            'empiece_conversaciones' => 'empC', 'haga_revisitas' => 'hRev',
            'haga_discipulos' => 'hDis', 'explique_creencias' => 'expC',
            'discurso_maestros' => 'dMae', 'discurso_vida' => 'dVid',
            'ayudante' => 'ayud', 'conductor_estudio' => 'cond', 'lector_estudio' => 'lecE',
            default => substr($tp, 0, 4),
        };
        $tipos .= "{$short}:{$c} ";
    }
    echo str_pad(mb_substr($d['nombre'], 0, 31), 32)
        . str_pad($d['nomb'], 8)
        . str_pad($d['total'], 5)
        . $tipos . "\n";
}

echo "\n=== FIN ===\n";
