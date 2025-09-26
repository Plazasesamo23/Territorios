<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TerritorioController;
use App\Http\Controllers\PublicadorController;
use App\Http\Controllers\RegistroController;
use App\Http\Controllers\S13Controller;

// Ruta principal - Dashboard (PRIMERA PRIORIDAD)
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Rutas específicas de territorios (ANTES del resource para evitar conflictos)
Route::get('territorios', [TerritorioController::class, 'index'])->name('territorios.index');
Route::get('territorios/create', [TerritorioController::class, 'create'])->name('territorios.create');
Route::post('territorios', [TerritorioController::class, 'store'])->name('territorios.store');
Route::get('territorios/{territorio}', [TerritorioController::class, 'show'])->name('territorios.show')->where('territorio', '[0-9]+');
Route::get('territorios/{territorio}/edit', [TerritorioController::class, 'edit'])->name('territorios.edit')->where('territorio', '[0-9]+');
Route::put('territorios/{territorio}', [TerritorioController::class, 'update'])->name('territorios.update')->where('territorio', '[0-9]+');
Route::delete('territorios/{territorio}', [TerritorioController::class, 'destroy'])->name('territorios.destroy')->where('territorio', '[0-9]+');
Route::post('territorios/{territorio}/enviar-whatsapp', [TerritorioController::class, 'enviarWhatsapp'])->name('territorios.whatsapp')->where('territorio', '[0-9]+');

// Rutas para Publicadores
Route::resource('publicadores', PublicadorController::class)->parameters([
    'publicadores' => 'publicador'
]);
// Ruta adicional para gestión de registros del publicador
Route::get('publicadores/{publicador}/registros', [PublicadorController::class, 'registros'])->name('publicadores.registros');

// Rutas para Registros
Route::resource('registros', RegistroController::class);
Route::get('registros-archivados', [RegistroController::class, 'archivados'])->name('registros.archivados');
Route::post('registros/{registro}/marcar-entrada', [RegistroController::class, 'marcarEntrada'])->name('registros.entrada');

// Ruta para S13 (Seguimiento)
// Rutas para S13
Route::get('s13', [S13Controller::class, 'index'])->name('s13.index');
Route::get('s13/generar-pdf', [S13Controller::class, 'generarPdf'])->name('s13.generar-pdf');
Route::get('s13/vista-previa', [S13Controller::class, 'vistaPrevia'])->name('s13.vista-previa');

// Ruta para Creador de Territorios
Route::get('creador-territorios', function () {
    return view('creador-territorios.simple');
})->name('creador-territorios.index');

// Ruta para Editor Visual de Territorios
Route::get('editor-visual-territorios', function () {
    return view('creador-territorios.visual-editor');
})->name('creador-territorios.visual-editor');

// Ruta para configuración (placeholder para futuro)
Route::get('configuracion', function () {
    return view('configuracion');
})->name('configuracion');
Route::post('configuracion', [DashboardController::class, 'guardarConfiguracion'])->name('configuracion.guardar');

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
