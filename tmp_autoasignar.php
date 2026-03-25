<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());
session(['congregacion_activa_id' => 1]);

$prog = App\Models\ReunionPrograma::find(6);
$svc = new App\Services\AsignacionReunionService(1);
$r = $svc->autoAsignar($prog);

echo "Asignados: {$r['asignados']}\n";
if ($r['sin_candidatos']) echo "Sin candidatos: " . implode(', ', $r['sin_candidatos']) . "\n";

$prog->refresh();
$prog->load('partes');
foreach ($prog->partes->where('seccion', 'maestros') as $p) {
    $pub = $p->publicador_id ? App\Models\Publicador::find($p->publicador_id) : null;
    $ayu = $p->ayudante_id ? App\Models\Publicador::find($p->ayudante_id) : null;
    echo $p->tipo . ': ' . ($pub ? $pub->nombre_completo . ' (' . $pub->genero . ')' : 'VACIO');
    echo ' + ' . ($ayu ? $ayu->nombre_completo . ' (' . $ayu->genero . ')' : '--') . "\n";
}
