<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$territorios = App\Models\Territorio::take(5)->get();

echo "<h2>Debug Territorios</h2>";
echo "<table border='1'>";
echo "<tr><th>ID</th><th>Numero</th><th>Tipo</th><th>Congr</th><th>Path Legacy</th><th>Existe?</th><th>URL Generada</th></tr>";

foreach ($territorios as $t) {
    $pathLegacy = "imagenes/" . $t->congregacion_id . "_" . $t->numero . ".jpg";
    $existe = file_exists(public_path($pathLegacy)) ? 'SI' : 'NO';
    $url = $t->getImagenUrl();

    echo "<tr>";
    echo "<td>{$t->id}</td>";
    echo "<td>{$t->numero}</td>";
    echo "<td>" . ($t->tipo ?? 'NULL') . "</td>";
    echo "<td>{$t->congregacion_id}</td>";
    echo "<td>{$pathLegacy}</td>";
    echo "<td>{$existe}</td>";
    echo "<td style='max-width:300px;overflow:hidden;'>{$url}</td>";
    echo "</tr>";
}
echo "</table>";
