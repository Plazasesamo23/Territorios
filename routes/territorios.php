<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Territorios\TerritorioController;
use App\Http\Controllers\Territorios\RegistroController;
use App\Http\Controllers\Territorios\S13Controller;

// Rutas de Territorios
Route::get('territorios', [TerritorioController::class, 'index'])->name('territorios.index');
Route::get('territorios/create', [TerritorioController::class, 'create'])->name('territorios.create');
Route::post('territorios', [TerritorioController::class, 'store'])->name('territorios.store');
Route::get('territorios/{territorio}', [TerritorioController::class, 'show'])->name('territorios.show')->where('territorio', '[0-9]+');
Route::get('territorios/{territorio}/edit', [TerritorioController::class, 'edit'])->name('territorios.edit')->where('territorio', '[0-9]+');
Route::put('territorios/{territorio}', [TerritorioController::class, 'update'])->name('territorios.update')->where('territorio', '[0-9]+');
Route::delete('territorios/{territorio}', [TerritorioController::class, 'destroy'])->name('territorios.destroy')->where('territorio', '[0-9]+');
Route::post('territorios/{territorio}/enviar-whatsapp', [TerritorioController::class, 'enviarWhatsapp'])->name('territorios.whatsapp')->where('territorio', '[0-9]+');

// Rutas de Registros
Route::resource('registros', RegistroController::class);
Route::get('registros-archivados', [RegistroController::class, 'archivados'])->name('registros.archivados');
Route::post('registros/{registro}/marcar-entrada', [RegistroController::class, 'marcarEntrada'])->name('registros.entrada');

// Rutas de S13
Route::get('s13', [S13Controller::class, 'index'])->name('s13.index');
Route::get('s13/generar-pdf', [S13Controller::class, 'generarPdf'])->name('s13.generar-pdf');
Route::get('s13/vista-previa', [S13Controller::class, 'vistaPrevia'])->name('s13.vista-previa');

// Creador y Editor de Territorios
Route::get('creador-territorios', function () {
    return view('creador-territorios.simple');
})->name('creador-territorios.index');

Route::get('editor-visual-territorios', function () {
    return view('creador-territorios.visual-editor');
})->name('creador-territorios.visual-editor');
