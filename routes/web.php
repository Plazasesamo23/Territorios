<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TerritorioController;
use App\Http\Controllers\PublicadorController;
use App\Http\Controllers\RegistroController;
use App\Http\Controllers\S13Controller;
use App\Http\Controllers\CongregacionController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\UsuarioController;

// Rutas de autenticación (Laravel UI/Breeze)
Auth::routes(['register' => false]); // Desactivar registro público

// Rutas protegidas con autenticación y filtro de congregación
Route::middleware(['auth', 'congregacion'])->group(function () {

    // Ruta principal - Dashboard (todos los usuarios)
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // =====================================================
    // RUTAS PARA TODOS LOS USUARIOS (user, admin, superadmin)
    // =====================================================

    // Ver territorios
    Route::get('territorios', [TerritorioController::class, 'index'])->name('territorios.index');
    Route::get('territorios/{territorio}', [TerritorioController::class, 'show'])->name('territorios.show')->where('territorio', '[0-9]+');

    // Ver publicadores
    Route::get('publicadores', [PublicadorController::class, 'index'])->name('publicadores.index');
    Route::get('publicadores/{publicador}', [PublicadorController::class, 'show'])->name('publicadores.show')->where('publicador', '[0-9]+');
    Route::get('publicadores/{publicador}/registros', [PublicadorController::class, 'registros'])->name('publicadores.registros')->where('publicador', '[0-9]+');

    // Editar publicadores (solo datos como teléfono)
    Route::get('publicadores/{publicador}/edit', [PublicadorController::class, 'edit'])->name('publicadores.edit')->where('publicador', '[0-9]+');
    Route::put('publicadores/{publicador}', [PublicadorController::class, 'update'])->name('publicadores.update')->where('publicador', '[0-9]+');

    // Registros - crear (asignar territorio) y marcar entrada
    Route::get('registros', [RegistroController::class, 'index'])->name('registros.index');
    Route::get('registros/create', [RegistroController::class, 'create'])->name('registros.create');
    Route::post('registros', [RegistroController::class, 'store'])->name('registros.store');
    Route::get('registros/{registro}', [RegistroController::class, 'show'])->name('registros.show');
    Route::post('registros/{registro}/marcar-entrada', [RegistroController::class, 'marcarEntrada'])->name('registros.entrada');
    Route::get('registros-archivados', [RegistroController::class, 'archivados'])->name('registros.archivados');

    // S13 - todos pueden generar
    Route::get('s13', [S13Controller::class, 'index'])->name('s13.index');
    Route::get('s13/generar-pdf', [S13Controller::class, 'generarPdf'])->name('s13.generar-pdf');
    Route::get('s13/vista-previa', [S13Controller::class, 'vistaPrevia'])->name('s13.vista-previa');

    // Enviar WhatsApp (todos)
    Route::post('territorios/{territorio}/enviar-whatsapp', [TerritorioController::class, 'enviarWhatsapp'])->name('territorios.whatsapp')->where('territorio', '[0-9]+');

    // Perfil personal (todos)
    Route::get('perfil', [PerfilController::class, 'index'])->name('perfil.index');
    Route::post('perfil/password', [PerfilController::class, 'cambiarPassword'])->name('perfil.password');
    Route::post('perfil/nombre', [PerfilController::class, 'actualizarNombre'])->name('perfil.nombre');

    // =====================================================
    // RUTAS SOLO PARA ADMIN Y SUPERADMIN
    // =====================================================
    Route::middleware('role:admin')->group(function () {

        // Crear/editar/eliminar territorios
        Route::get('territorios/create', [TerritorioController::class, 'create'])->name('territorios.create');
        Route::post('territorios', [TerritorioController::class, 'store'])->name('territorios.store');
        Route::get('territorios/{territorio}/edit', [TerritorioController::class, 'edit'])->name('territorios.edit')->where('territorio', '[0-9]+');
        Route::put('territorios/{territorio}', [TerritorioController::class, 'update'])->name('territorios.update')->where('territorio', '[0-9]+');
        Route::delete('territorios/{territorio}', [TerritorioController::class, 'destroy'])->name('territorios.destroy')->where('territorio', '[0-9]+');

        // Crear/eliminar publicadores
        Route::get('publicadores/create', [PublicadorController::class, 'create'])->name('publicadores.create');
        Route::post('publicadores', [PublicadorController::class, 'store'])->name('publicadores.store');
        Route::delete('publicadores/{publicador}', [PublicadorController::class, 'destroy'])->name('publicadores.destroy')->where('publicador', '[0-9]+');

        // Editar/eliminar registros
        Route::get('registros/{registro}/edit', [RegistroController::class, 'edit'])->name('registros.edit');
        Route::put('registros/{registro}', [RegistroController::class, 'update'])->name('registros.update');
        Route::delete('registros/{registro}', [RegistroController::class, 'destroy'])->name('registros.destroy')->where('registro', '[0-9]+');

        // Gestión de usuarios de la congregación
        Route::get('usuarios', [UsuarioController::class, 'index'])->name('usuarios.index');
        Route::get('usuarios/create', [UsuarioController::class, 'create'])->name('usuarios.create');
        Route::post('usuarios', [UsuarioController::class, 'store'])->name('usuarios.store');
        Route::get('usuarios/{usuario}/edit', [UsuarioController::class, 'edit'])->name('usuarios.edit');
        Route::put('usuarios/{usuario}', [UsuarioController::class, 'update'])->name('usuarios.update');
        Route::delete('usuarios/{usuario}', [UsuarioController::class, 'destroy'])->name('usuarios.destroy');

        // Configuración de la congregación
        Route::get('configuracion', [DashboardController::class, 'configuracion'])->name('configuracion');
        Route::post('configuracion', [DashboardController::class, 'guardarConfiguracion'])->name('configuracion.guardar');
        Route::post('configuracion/whatsapp', [DashboardController::class, 'guardarMensajeWhatsapp'])->name('configuracion.guardar-whatsapp');

        // Creador de Territorios
        Route::get('creador-territorios', function () {
            return view('creador-territorios.editor');
        })->name('creador-territorios.index');

        Route::get('creador-territorios/editor', function () {
            return view('creador-territorios.editor');
        })->name('creador-territorios.editor');
    });

    // =====================================================
    // RUTAS SOLO PARA SUPERADMIN
    // =====================================================
    Route::middleware('can:superadmin')->group(function () {
        Route::resource('congregaciones', CongregacionController::class)->parameters([
            'congregaciones' => 'congregacion'
        ]);
    });

    // Ruta para cambiar congregación (superadmin)
    Route::post('congregaciones/{congregacion}/cambiar', [CongregacionController::class, 'cambiar'])
        ->name('congregaciones.cambiar');

    // Ruta de referencia UI (desarrollo)
    Route::get('referencia-ui', function () {
        return view('referencia-ui');
    })->name('referencia-ui');
});
