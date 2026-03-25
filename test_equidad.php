<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$congregacionId = 1;

// Historial de asignaciones por publicador
$historial = DB::table('reuniones_historial')
    ->where('congregacion_id', $congregacionId)
    ->selectRaw('publicador_id, tipo_parte, count(*) as total')
    ->groupBy('publicador_id', 'tipo_parte')
    ->get();

if ($historial->isEmpty()) {
    echo "No hay historial de asignaciones aun.\n";
    echo "El algoritmo no se ha usado todavia.\n\n";

    // Verificar cuantos candidatos hay por tipo
    echo "=== CANDIDATOS POR TIPO (segun autorizaciones) ===\n";
    $tipos = DB::table('reuniones_autorizaciones')
        ->where('congregacion_id', $congregacionId)
        ->selectRaw('tipo_parte, count(*) as total')
        ->groupBy('tipo_parte')
        ->orderBy('total')
        ->get();
    foreach ($tipos as $t) {
        echo "  {$t->tipo_parte}: {$t->total} publicadores\n";
    }

    // Simular equidad: con cuantos candidatos compite cada tipo
    echo "\n=== ANALISIS DE EQUILIBRIO POTENCIAL ===\n";
    echo "Tipos con POCOS candidatos (se repetiran mas):\n";
    foreach ($tipos as $t) {
        if ($t->total <= 10) {
            echo "  ⚠ {$t->tipo_parte}: solo {$t->total} personas. Cada una hara esta parte ~1 vez cada {$t->total} semanas\n";
        }
    }
    echo "\nTipos con MUCHOS candidatos (buena rotacion):\n";
    foreach ($tipos as $t) {
        if ($t->total > 10) {
            echo "  ✓ {$t->tipo_parte}: {$t->total} personas\n";
        }
    }
} else {
    // Hay historial - analizar distribucion
    $porPublicador = $historial->groupBy('publicador_id');
    $totales = [];
    foreach ($porPublicador as $pubId => $items) {
        $totales[$pubId] = $items->sum('total');
    }

    $media = array_sum($totales) / count($totales);
    $min = min($totales);
    $max = max($totales);

    echo "=== DISTRIBUCION DE ASIGNACIONES ===\n";
    echo "  Publicadores con historial: " . count($totales) . "\n";
    echo "  Media: " . round($media, 1) . "\n";
    echo "  Min: {$min} | Max: {$max}\n";
    echo "  Ratio max/min: " . ($min > 0 ? round($max / $min, 1) : 'INF') . "\n\n";

    // Top 5 mas asignados
    arsort($totales);
    echo "Top 5 mas asignados:\n";
    $i = 0;
    foreach ($totales as $pubId => $total) {
        if ($i++ >= 5) break;
        $nombre = DB::table('publicadores')->where('id', $pubId)->value('nombre');
        $apellidos = DB::table('publicadores')->where('id', $pubId)->value('apellidos');
        echo "  {$nombre} {$apellidos}: {$total} asignaciones\n";
    }

    // Bottom 5
    asort($totales);
    echo "\nTop 5 menos asignados:\n";
    $i = 0;
    foreach ($totales as $pubId => $total) {
        if ($i++ >= 5) break;
        $nombre = DB::table('publicadores')->where('id', $pubId)->value('nombre');
        $apellidos = DB::table('publicadores')->where('id', $pubId)->value('apellidos');
        echo "  {$nombre} {$apellidos}: {$total} asignaciones\n";
    }
}

echo "\n=== PROBLEMAS DEL ALGORITMO ACTUAL ===\n";
echo "1. Equidad usa conteo GLOBAL (todos los tipos juntos), no por tipo.\n";
echo "   Alguien con 'presidente' + 'oracion' + 'tesoros' acumula mas que\n";
echo "   alguien que solo puede hacer 'maestros'. La media se calcula\n";
echo "   sobre los candidatos del tipo actual, pero el conteo mezcla todo.\n\n";
echo "2. Solo mira 6 meses de historial. Si alguien hizo muchas partes\n";
echo "   hace 7 meses, se 'resetea' su conteo y vuelve a recibir muchas.\n\n";
echo "3. El jitter aleatorio (+-2 puntos) puede hacer que con puntuaciones\n";
echo "   cercanas, la seleccion sea semi-aleatoria en vez de equitativa.\n\n";
echo "4. Ancianos tienen -50 en partes de estudiantes pero siguen pudiendo\n";
echo "   acumular muchas partes de otros tipos (presidente, oracion, tesoros,\n";
echo "   perlas, discurso_vida, conductor). Son elegibles para ~6 tipos vs\n";
echo "   un publicador normal que solo es elegible para ~2-3.\n";
