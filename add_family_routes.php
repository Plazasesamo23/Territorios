<?php
$file = file_get_contents('routes/web.php');

$familyRoutes = <<<'ROUTES'

// Family relationships routes
Route::get('publicadores/{publicador}/familiares', [App\Http\Controllers\PublicadorController::class, 'familiares'])->name('publicadores.familiares');
Route::get('publicadores/{publicador}/disponibles-familia', [App\Http\Controllers\PublicadorController::class, 'disponiblesFamilia'])->name('publicadores.disponibles-familia');
Route::post('publicadores/{publicador}/add-familiar', [App\Http\Controllers\PublicadorController::class, 'addFamiliar'])->name('publicadores.add-familiar');
Route::post('publicadores/{publicador}/remove-familiar', [App\Http\Controllers\PublicadorController::class, 'removeFamiliar'])->name('publicadores.remove-familiar');

ROUTES;

// Check if routes already exist
if (strpos($file, 'familiares') !== false) {
    echo "Family routes already exist\n";
    exit;
}

// Find the last publicadores route and add after it
if (preg_match('/Route::[^;]+publicadores[^;]+;/s', $file, $matches, PREG_OFFSET_MATCH)) {
    // Find the last occurrence
    preg_match_all('/Route::[^;]+publicadores[^;]+;/s', $file, $allMatches, PREG_OFFSET_CAPTURE);
    $lastMatch = end($allMatches[0]);
    $insertPos = $lastMatch[1] + strlen($lastMatch[0]);

    $file = substr($file, 0, $insertPos) . $familyRoutes . substr($file, $insertPos);
    file_put_contents('routes/web.php', $file);
    echo "Family routes added successfully\n";
} else {
    // Fallback: add before the last closing
    $file = rtrim($file) . "\n" . $familyRoutes;
    file_put_contents('routes/web.php', $file);
    echo "Family routes added at end of file\n";
}
