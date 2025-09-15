<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Http\Kernel::class)->bootstrap();

header('Content-Type: text/html');

echo '<h1>🔧 DEBUG CONFIGURACIÓN</h1>';
echo '<p><strong>Días archivo configurados:</strong> ' . config('territorios.dias_archivo') . '</p>';
echo '<p><strong>Fecha actual:</strong> ' . now() . '</p>';

$territorio1 = \App\Models\Territorio::find(1);
if ($territorio1) {
    $ultimoRegistro = $territorio1->registros()->whereNotNull('fecha_entrada')->latest('fecha_entrada')->first();
    if ($ultimoRegistro) {
        $diasDesdeDevolucion = \Carbon\Carbon::parse($ultimoRegistro->fecha_entrada)->diffInDays(now());
        echo '<hr>';
        echo '<h2>🏠 Territorio #1</h2>';
        echo '<p><strong>Última devolución:</strong> ' . $ultimoRegistro->fecha_entrada . '</p>';
        echo '<p><strong>Días desde devolución:</strong> ' . $diasDesdeDevolucion . '</p>';
        echo '<p><strong>Días configurados archivo:</strong> ' . config('territorios.dias_archivo') . '</p>';
        echo '<p><strong>¿Cumple 90 días?:</strong> ' . ($diasDesdeDevolucion >= config('territorios.dias_archivo') ? 'SÍ' : 'NO') . '</p>';
        echo '<p><strong>Estado calculado:</strong> ' . $territorio1->calcularEstado() . '</p>';
        echo '<p><strong>¿Disponible?:</strong> ' . ($territorio1->estaDisponibleParaAsignar() ? 'SÍ' : 'NO') . '</p>';
    }
}

echo '<hr>';
echo '<p><a href="/territorios/public/territorios">Ver territorios</a></p>';

