<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Reuniones\ReunionController;

Route::middleware(['auth', 'congregacion'])->prefix('reuniones')->group(function () {

    Route::get('/', [ReunionController::class, 'index'])->name('reuniones.index');
    Route::get('/crear', [ReunionController::class, 'create'])->name('reuniones.create');
    Route::post('/', [ReunionController::class, 'store'])->name('reuniones.store');

    // Generos bulk
    Route::get('/generos', [ReunionController::class, 'generosBulk'])->name('reuniones.generos');
    Route::post('/generos', [ReunionController::class, 'guardarGeneros'])->name('reuniones.generos.guardar');

    // CRUD programas (resource-style)
    Route::get('/{reunione}', [ReunionController::class, 'show'])->name('reuniones.show');
    Route::get('/{reunione}/editar', [ReunionController::class, 'edit'])->name('reuniones.edit');
    Route::put('/{reunione}', [ReunionController::class, 'update'])->name('reuniones.update');
    Route::delete('/{reunione}', [ReunionController::class, 'destroy'])->name('reuniones.destroy');

    // Acciones especiales
    Route::post('/{reunione}/auto-asignar', [ReunionController::class, 'autoAsignar'])->name('reuniones.auto-asignar');
    Route::post('/{reunione}/publicar', [ReunionController::class, 'publicar'])->name('reuniones.publicar');
});
