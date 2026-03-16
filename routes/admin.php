<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\PublicadorController;
use App\Http\Controllers\DashboardController;

// Rutas de Publicadores
Route::resource('publicadores', PublicadorController::class)->parameters([
    'publicadores' => 'publicador'
]);
Route::get('publicadores/{publicador}/registros', [PublicadorController::class, 'registros'])->name('publicadores.registros');

// Configuración
Route::get('configuracion', function () {
    return view('configuracion');
})->name('configuracion');
Route::post('configuracion', [DashboardController::class, 'guardarConfiguracion'])->name('configuracion.guardar');
