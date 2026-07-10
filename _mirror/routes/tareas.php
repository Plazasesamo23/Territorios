<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TareaController;

Route::middleware(['auth', 'congregacion'])->prefix('tareas')->group(function () {
    Route::get('/', [TareaController::class, 'index'])->name('tareas.index');
    Route::get('/widget', [TareaController::class, 'widget'])->name('tareas.widget');
    Route::post('/', [TareaController::class, 'store'])->name('tareas.store');
    Route::put('/{tarea}', [TareaController::class, 'update'])->name('tareas.update');
    Route::delete('/{tarea}', [TareaController::class, 'destroy'])->name('tareas.destroy');
    Route::post('/{tarea}/toggle-hecha', [TareaController::class, 'toggleHecha'])->name('tareas.toggle-hecha');
    Route::post('/{tarea}/anclar', [TareaController::class, 'toggleAnclar'])->name('tareas.anclar');
    Route::post('/{tarea}/estado', [TareaController::class, 'cambiarEstado'])->name('tareas.cambiar-estado');
});
