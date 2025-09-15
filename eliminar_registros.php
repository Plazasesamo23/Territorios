<?php
// Script temporal para eliminar registros específicos
require_once 'vendor/autoload.php';

use App\Models\Registro;
use App\Models\Publicador;
use App\Models\Territorio;

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🔍 Buscando registros a eliminar...\n";

// Buscar Bryan
$bryan = Publicador::where('nombre', 'LIKE', '%bryan%')
    ->orWhere('nombre', 'LIKE', '%Bryan%')
    ->first();

if ($bryan) {
    echo "✅ Bryan encontrado: {$bryan->nombre}\n";
    
    // Buscar territorio 7
    $territorio7 = Territorio::where('numero', 7)->first();
    if ($territorio7) {
        $registros7 = Registro::where('publicador_id', $bryan->id)
            ->where('territorio_id', $territorio7->id)
            ->get();
        
        foreach ($registros7 as $registro) {
            echo "🗑️ Eliminando registro ID {$registro->id} - Territorio 7 - Bryan\n";
            $registro->delete();
        }
    }
} else {
    echo "❌ Bryan no encontrado\n";
}

// Buscar Ana
$ana = Publicador::where('nombre', 'LIKE', '%ana%')
    ->orWhere('nombre', 'LIKE', '%Ana%')
    ->first();

if ($ana) {
    echo "✅ Ana encontrada: {$ana->nombre}\n";
    
    // Buscar territorio 45
    $territorio45 = Territorio::where('numero', 45)->first();
    if ($territorio45) {
        $registros45 = Registro::where('publicador_id', $ana->id)
            ->where('territorio_id', $territorio45->id)
            ->get();
        
        foreach ($registros45 as $registro) {
            echo "🗑️ Eliminando registro ID {$registro->id} - Territorio 45 - Ana\n";
            $registro->delete();
        }
    }
} else {
    echo "❌ Ana no encontrada\n";
}

echo "✅ Proceso completado\n";
