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
Route::get('s13', [S13Controller::class, 'index'])->name('s13.index');

// Ruta para configuración (placeholder para futuro)
Route::get('configuracion', function () {
    return view('configuracion');
})->name('configuracion');
Route::post('configuracion', [DashboardController::class, 'guardarConfiguracion'])->name('configuracion.guardar');

// Ruta de referencia UI
Route::get('referencia-ui', function () {
    return view('referencia-ui');
})->name('referencia-ui');
