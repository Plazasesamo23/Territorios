<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CambiarUsuarioController;
use App\Http\Controllers\PerfilController;

// Rutas de autenticacion (Laravel UI/Breeze)
Auth::routes(['register' => false]);

// Rutas protegidas con autenticacion y filtro de congregacion
Route::middleware(['auth', 'congregacion'])->group(function () {

    // Ruta principal - Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Perfil personal
    Route::get('perfil', [PerfilController::class, 'index'])->name('perfil.index');
    Route::post('perfil/password', [PerfilController::class, 'cambiarPassword'])->name('perfil.password');
    Route::post('perfil/nombre', [PerfilController::class, 'actualizarNombre'])->name('perfil.nombre');

    // Cambiar usuario (estilo Netflix)
    Route::get('cambiar-usuario', [CambiarUsuarioController::class, 'index'])->name('cambiar-usuario.index');
    Route::post('cambiar-usuario', [CambiarUsuarioController::class, 'cambiar'])->name('cambiar-usuario.cambiar');
    Route::get('cambiar-usuario/volver', [CambiarUsuarioController::class, 'volver'])->name('cambiar-usuario.volver');

    // Ruta de referencia UI (desarrollo)
    Route::get('referencia-ui', function () {
        return view('referencia-ui');
    })->name('referencia-ui');
});
