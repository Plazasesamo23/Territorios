<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Reuniones\ReunionController;

Route::middleware(['auth', 'congregacion'])->prefix('reuniones')->group(function () {

    // VyM (entre semana)
    Route::get('/', [ReunionController::class, 'index'])->name('reuniones.index');
    Route::get('/crear', [ReunionController::class, 'create'])->name('reuniones.create');
    Route::post('/', [ReunionController::class, 'store'])->name('reuniones.store');

    // Fin de semana
    Route::get('/fin-de-semana', [ReunionController::class, 'finSemana'])->name('reuniones.finsemana');

    // Asignaciones (vista general)
    Route::get('/asignaciones', [ReunionController::class, 'asignaciones'])->name('reuniones.asignaciones');

    // Historial por publicador
    Route::get('/historial/{publicador}', [ReunionController::class, 'historialPublicador'])->name('reuniones.historial');

    // Autorizaciones (drag & drop)
    Route::get('/autorizaciones', [ReunionController::class, 'autorizaciones'])->name('reuniones.autorizaciones');
    Route::post('/autorizaciones', [ReunionController::class, 'guardarAutorizacion'])->name('reuniones.autorizaciones.guardar');

    // Generos bulk
    Route::get('/generos', [ReunionController::class, 'generosBulk'])->name('reuniones.generos');
    Route::post('/generos', [ReunionController::class, 'guardarGeneros'])->name('reuniones.generos.guardar');

    // CRUD programas VyM
    Route::get('/{reunione}', [ReunionController::class, 'show'])->name('reuniones.show');
    Route::get('/{reunione}/editar', [ReunionController::class, 'edit'])->name('reuniones.edit');
    Route::put('/{reunione}', [ReunionController::class, 'update'])->name('reuniones.update');
    Route::delete('/{reunione}', [ReunionController::class, 'destroy'])->name('reuniones.destroy');

    // Acciones especiales
    Route::post('/{reunione}/auto-asignar', [ReunionController::class, 'autoAsignar'])->name('reuniones.auto-asignar');
    Route::post('/{reunione}/importar-titulos', [ReunionController::class, 'importarTitulos'])->name('reuniones.importar-titulos');
    Route::get('/{reunione}/importar-titulos', [ReunionController::class, 'edit'])->name('reuniones.importar-titulos.get');
    Route::post('/{reunione}/publicar', [ReunionController::class, 'publicar'])->name('reuniones.publicar');
    Route::get('/{reunione}/recomendar/{tipoParte}', [ReunionController::class, 'recomendar'])->name('reuniones.recomendar');
});
