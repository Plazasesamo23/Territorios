<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CambiarUsuarioController;
use App\Http\Controllers\PerfilController;

// Ruta para refrescar el token CSRF (evita error 419 en login)
Route::get('/refresh-csrf', function () {
    return response()->json(['token' => csrf_token()]);
});

// Rutas de autenticacion (Laravel UI/Breeze)
Auth::routes(['register' => false]);

// Rutas protegidas con autenticacion y filtro de congregacion
Route::middleware(['auth', 'congregacion'])->group(function () {

    // Ruta principal - Menu inicio
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/servicio', [DashboardController::class, 'servicio'])->name('servicio');

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
