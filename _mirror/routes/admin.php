<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\PublicadorController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\Admin\GrupoPredicacionController;
use App\Http\Controllers\Admin\CongregacionController;
use App\Http\Controllers\DashboardController;

Route::middleware(['auth', 'congregacion'])->group(function () {

    // =====================================================
    // PUBLICADORES - Acceso para todos los usuarios
    // =====================================================
    Route::get('publicadores', [PublicadorController::class, 'index'])->name('publicadores.index');
    Route::get('publicadores/{publicador}', [PublicadorController::class, 'show'])->name('publicadores.show')->where('publicador', '[0-9]+');
    Route::post('publicadores/{publicador}/toggle-precursor', [PublicadorController::class, 'togglePrecursor'])->name('publicadores.togglePrecursor');
    Route::get('publicadores/{publicador}/registros', [PublicadorController::class, 'registros'])->name('publicadores.registros')->where('publicador', '[0-9]+');

    // Editar publicadores (solo datos como telefono)
    Route::get('publicadores/{publicador}/edit', [PublicadorController::class, 'edit'])->name('publicadores.edit')->where('publicador', '[0-9]+');
    Route::put('publicadores/{publicador}', [PublicadorController::class, 'update'])->name('publicadores.update')->where('publicador', '[0-9]+');

    // Family relationships routes
    Route::get('publicadores/{publicador}/familiares', [PublicadorController::class, 'familiares'])->name('publicadores.familiares');
    Route::get('publicadores/{publicador}/disponibles-familia', [PublicadorController::class, 'disponiblesFamilia'])->name('publicadores.disponibles-familia');
    Route::post('publicadores/{publicador}/add-familiar', [PublicadorController::class, 'addFamiliar'])->name('publicadores.add-familiar');
    Route::post('publicadores/{publicador}/remove-familiar', [PublicadorController::class, 'removeFamiliar'])->name('publicadores.remove-familiar');

    // =====================================================
    // RUTAS SOLO PARA ADMIN Y SUPERADMIN
    // =====================================================
    Route::middleware('role:admin')->group(function () {

        // Pagina principal de Administracion
        Route::get('administracion', function () {
            return view('administracion');
        })->name('administracion');

        // Crear/eliminar publicadores
        Route::get('publicadores/create', [PublicadorController::class, 'create'])->name('publicadores.create');
        Route::post('publicadores', [PublicadorController::class, 'store'])->name('publicadores.store');
        Route::delete('publicadores/{publicador}', [PublicadorController::class, 'destroy'])->name('publicadores.destroy')->where('publicador', '[0-9]+');

        // Gestion de usuarios de la congregacion
        Route::get('usuarios', [UsuarioController::class, 'index'])->name('usuarios.index');
        Route::get('usuarios/create', [UsuarioController::class, 'create'])->name('usuarios.create');
        Route::post('usuarios', [UsuarioController::class, 'store'])->name('usuarios.store');
        Route::get('usuarios/{usuario}/edit', [UsuarioController::class, 'edit'])->name('usuarios.edit');
        Route::put('usuarios/{usuario}', [UsuarioController::class, 'update'])->name('usuarios.update');
        Route::delete('usuarios/{usuario}', [UsuarioController::class, 'destroy'])->name('usuarios.destroy');

        // Configuracion de la congregacion
        Route::get('configuracion', [DashboardController::class, 'configuracion'])->name('configuracion');
        Route::post('configuracion', [DashboardController::class, 'guardarConfiguracion'])->name('configuracion.guardar');
        Route::post('configuracion/whatsapp', [DashboardController::class, 'guardarMensajeWhatsapp'])->name('configuracion.guardar-whatsapp');

        // Grupos de Predicacion
        Route::get('grupos-predicacion', [GrupoPredicacionController::class, 'index'])->name('grupos-predicacion.index');
        Route::post('grupos-predicacion/mover', [GrupoPredicacionController::class, 'moverPublicador'])->name('grupos-predicacion.mover');
        Route::post('grupos-predicacion/toggle-rol', [GrupoPredicacionController::class, 'toggleRol'])->name('grupos-predicacion.toggleRol');
        Route::post('grupos-predicacion/reordenar', [GrupoPredicacionController::class, 'reordenar'])->name('grupos-predicacion.reordenar');
        Route::get('grupos-predicacion/estadisticas/{id}', [GrupoPredicacionController::class, 'estadisticas'])->name('grupos-predicacion.estadisticas');

        // Historial y generacion automatica de grupos
        Route::post('grupos-predicacion/guardar-historial', [GrupoPredicacionController::class, 'guardarHistorial'])->name('grupos-predicacion.guardarHistorial');
        Route::post('grupos-predicacion/generar-automatico', [GrupoPredicacionController::class, 'generarAutomatico'])->name('grupos-predicacion.generarAutomatico');

        // Gestion de historial de anos
        Route::get('grupos-predicacion/historial', [GrupoPredicacionController::class, 'listarHistorial'])->name('grupos-predicacion.historial');
        Route::post('grupos-predicacion/historial/crear', [GrupoPredicacionController::class, 'crearAnoHistorial'])->name('grupos-predicacion.historial.crear');
        Route::get('grupos-predicacion/historial/{ano}/editar', [GrupoPredicacionController::class, 'editarHistorial'])->name('grupos-predicacion.historial.editar');
        Route::post('grupos-predicacion/historial/actualizar', [GrupoPredicacionController::class, 'actualizarHistorial'])->name('grupos-predicacion.historial.actualizar');
        Route::delete('grupos-predicacion/historial/{ano}', [GrupoPredicacionController::class, 'eliminarAnoHistorial'])->name('grupos-predicacion.historial.eliminar');
    });

    // =====================================================
    // RUTAS SOLO PARA SUPERADMIN
    // =====================================================
    Route::middleware('can:superadmin')->group(function () {
        Route::resource('congregaciones', CongregacionController::class)->parameters([
            'congregaciones' => 'congregacion'
        ]);
    });

    // Ruta para cambiar congregacion (superadmin)
    Route::post('congregaciones/{congregacion}/cambiar', [CongregacionController::class, 'cambiar'])
        ->name('congregaciones.cambiar');
});
