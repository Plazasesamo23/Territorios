<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Mostrar primeros 10 territorios con sus URLs de imagen
$territorios = App\Models\Territorio::select('id', 'congregacion_id', 'numero', 'tipo')->take(15)->get();
foreach ($territorios as $t) {
    $url = $t->getImagenUrl();
    echo "ID:{$t->id} Cong:{$t->congregacion_id} Num:[{$t->numero}] Tipo:[{$t->tipo}] => {$url}\n";
}
