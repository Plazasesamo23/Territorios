<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$territorios = App\Models\Territorio::where('congregacion_id', 1)
    ->where(function($q) {
        $q->whereNull('tipo')->orWhere('tipo', 'normal');
    })
    ->orderBy('numero')
    ->take(15)
    ->get(['id', 'numero', 'tipo', 'imagen_url']);

foreach ($territorios as $t) {
    $imgUrl = $t->getImagenUrl();
    $tieneImgDb = !empty($t->imagen_url) ? 'SI' : 'NO';
    echo "T-{$t->numero} | img_url_db: {$tieneImgDb} | URL: {$imgUrl}\n";
    if (!empty($t->imagen_url)) {
        echo "   -> imagen_url: {$t->imagen_url}\n";
    }
}
