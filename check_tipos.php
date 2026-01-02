<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$territorios = App\Models\Territorio::select('numero', 'tipo')->take(20)->get();
foreach ($territorios as $t) {
    echo $t->numero . ' => tipo: [' . ($t->tipo ?? 'NULL') . ']' . PHP_EOL;
}
