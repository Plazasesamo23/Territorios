<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

// Ruta principal - Dashboard
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Ruta de referencia UI
Route::get('referencia-ui', function () {
    return view('referencia-ui');
})->name('referencia-ui');

// Ruta de debug para verificar regla de 90 días
Route::get('debug-90-dias', function() {
    $territorio1 = \App\Models\Territorio::where('numero', 1)->first();

    if (!$territorio1) {
        return 'Territorio #1 no encontrado';
    }

    $estado = $territorio1->calcularEstado();
    $disponible = $territorio1->estaDisponibleParaAsignar();
    $ultimoRegistro = $territorio1->registros()->whereNotNull('fecha_entrada')->latest('fecha_entrada')->first();

    $html = '<h1>DEBUG REGLA 90 DÍAS - ' . now() . '</h1>';
    $html .= '<p><strong>Territorio #1:</strong></p>';
    $html .= '<p>Estado calculado: <strong>' . $estado . '</strong></p>';
    $html .= '<p>Disponible para asignar: <strong>' . ($disponible ? 'SÍ' : 'NO') . '</strong></p>';

    if ($ultimoRegistro) {
        $diasDesdeDevolucion = \Carbon\Carbon::parse($ultimoRegistro->fecha_entrada)->diffInDays(now());
        $html .= '<p>Último registro devuelto: ' . $ultimoRegistro->fecha_entrada . '</p>';
        $html .= '<p>Días desde devolución: <strong>' . round($diasDesdeDevolucion, 2) . '</strong></p>';
        $html .= '<p>Debe esperar 90 días: <strong>' . ($diasDesdeDevolucion < 90 ? 'SÍ (EN ARCHIVO)' : 'NO (LIBRE)') . '</strong></p>';
    }

    $html .= '<p><a href="/territorios">Ver territorios</a> | <a href="/territorios?estado=archivo">Ver en archivo</a></p>';

    return $html;
});
