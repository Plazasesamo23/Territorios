<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Territorios\TerritorioController;
use App\Http\Controllers\Territorios\RegistroController;
use App\Http\Controllers\Territorios\S13Controller;
use App\Http\Controllers\Territorios\S13ImportController;
use App\Http\Controllers\Territorios\PanelTerritoriosController;

Route::middleware(['auth', 'congregacion'])->group(function () {

    // Panel especial para usuarios de territorios
    Route::get('/panel-territorios', [PanelTerritoriosController::class, 'index'])->name('panel-territorios');

    // Ver territorios (todos los usuarios)
    Route::get('territorios', [TerritorioController::class, 'index'])->name('territorios.index');
    Route::get('territorios/{territorio}', [TerritorioController::class, 'show'])->name('territorios.show')->where('territorio', '[0-9]+');

    // Registros - crear (asignar territorio) y marcar entrada
    Route::get('registros', [RegistroController::class, 'index'])->name('registros.index');
    Route::get('registros/create', [RegistroController::class, 'create'])->name('registros.create');
    Route::post('registros', [RegistroController::class, 'store'])->name('registros.store');
    Route::get('registros/{registro}', [RegistroController::class, 'show'])->name('registros.show');
    Route::post('registros/{registro}/marcar-entrada', [RegistroController::class, 'marcarEntrada'])->name('registros.entrada');
    Route::get('registros-archivados', [RegistroController::class, 'archivados'])->name('registros.archivados');

    // S13
    Route::get('s13', [S13Controller::class, 'index'])->name('s13.index');
    Route::get('s13/generar-pdf', [S13Controller::class, 'generarPdf'])->name('s13.generar-pdf');
    Route::get('s13/vista-previa', [S13Controller::class, 'vistaPrevia'])->name('s13.vista-previa');

    // Importacion S13
    Route::get('s13/importar', [S13ImportController::class, 'index'])->name('s13.importar');
    Route::post('s13/importar/procesar', [S13ImportController::class, 'procesar'])->name('s13.importar.procesar');
    Route::post('s13/importar/procesar-ocr', [S13ImportController::class, 'procesarOcr'])->name('s13.importar.procesar-ocr');
    Route::post('s13/importar/confirmar', [S13ImportController::class, 'confirmar'])->name('s13.importar.confirmar');
    Route::post('s13/importar/guardar-rapido', [S13ImportController::class, 'guardarRapido'])->name('s13.importar.guardar-rapido');

    // Enviar WhatsApp
    Route::post('territorios/{territorio}/enviar-whatsapp', [TerritorioController::class, 'enviarWhatsapp'])->name('territorios.whatsapp')->where('territorio', '[0-9]+');

    // =====================================================
    // RUTAS ADMIN - Crear/editar/eliminar territorios y registros
    // =====================================================
    Route::middleware('role:admin')->group(function () {
        Route::get('territorios/create', [TerritorioController::class, 'create'])->name('territorios.create');
        Route::post('territorios', [TerritorioController::class, 'store'])->name('territorios.store');
        Route::get('territorios/{territorio}/edit', [TerritorioController::class, 'edit'])->name('territorios.edit')->where('territorio', '[0-9]+');
        Route::put('territorios/{territorio}', [TerritorioController::class, 'update'])->name('territorios.update')->where('territorio', '[0-9]+');
        Route::delete('territorios/{territorio}', [TerritorioController::class, 'destroy'])->name('territorios.destroy')->where('territorio', '[0-9]+');

        Route::get('registros/{registro}/edit', [RegistroController::class, 'edit'])->name('registros.edit');
        Route::put('registros/{registro}', [RegistroController::class, 'update'])->name('registros.update');
        Route::delete('registros/{registro}', [RegistroController::class, 'destroy'])->name('registros.destroy')->where('registro', '[0-9]+');

        // Creador de Territorios
        Route::get('creador-territorios', function () {
            return view('creador-territorios.editor');
        })->name('creador-territorios.index');

        Route::get('creador-territorios/editor', function () {
            return view('creador-territorios.editor');
        })->name('creador-territorios.editor');
    });
});
