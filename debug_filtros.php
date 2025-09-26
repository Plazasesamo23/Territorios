<?php
require_once 'vendor/autoload.php';

use App\Models\Territorio;

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🔍 DEBUGGER: FILTROS DE TERRITORIOS\n";
echo str_repeat("=", 60) . "\n";

// Simular los diferentes filtros
$filtros = ['', 'libre', 'activo', 'atrasado', 'archivo'];

foreach ($filtros as $filtro) {
    echo "\n📊 FILTRO: " . ($filtro ?: 'TODOS') . "\n";
    echo str_repeat("-", 40) . "\n";
    
    if ($filtro) {
        // Simular el filtro del controlador
        $allTerritorios = Territorio::with(['registros.publicador'])->get();
        $territoriosFiltrados = $allTerritorios->filter(function($territorio) use ($filtro) {
            return $territorio->calcularEstado() === $filtro;
        });
        $territorios = $territoriosFiltrados->take(5);
    } else {
        // Sin filtro
        $territorios = Territorio::with(['registros.publicador'])->take(5)->get();
    }
    
    foreach ($territorios as $territorio) {
        $estado = $territorio->calcularEstado();
        $numero = $territorio->numero;
        echo "#{$numero} - Estado: {$estado}";
        
        if ($numero == 1) {
            echo " ← TERRITORIO #1 AQUÍ";
        }
        echo "\n";
    }
    
    echo "Total encontrados: " . $territorios->count() . "\n";
}

// Verificar específicamente el territorio #1
echo "\n🎯 TERRITORIO #1 ESPECÍFICO:\n";
$territorio1 = Territorio::where('numero', 1)->first();
if ($territorio1) {
    echo "Estado calculado: " . $territorio1->calcularEstado() . "\n";
    echo "Disponible para asignar: " . ($territorio1->estaDisponibleParaAsignar() ? 'SÍ' : 'NO') . "\n";
    echo "Aparece en filtro 'libre': " . ($territorio1->calcularEstado() === 'libre' ? 'SÍ' : 'NO') . "\n";
    echo "Aparece en filtro 'archivo': " . ($territorio1->calcularEstado() === 'archivo' ? 'SÍ' : 'NO') . "\n";
}

echo "\n✅ Debug completado\n";

