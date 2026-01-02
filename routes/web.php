<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GrupoPredicacionController;
use App\Http\Controllers\CambiarUsuarioController;
use App\Http\Controllers\TerritorioController;
use App\Http\Controllers\PublicadorController;
use App\Http\Controllers\RegistroController;
use App\Http\Controllers\S13Controller;
use App\Http\Controllers\S13ImportController;
use App\Http\Controllers\CongregacionController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\TurnoController;
use App\Http\Controllers\PanelTerritoriosController;

// Rutas de autenticación (Laravel UI/Breeze)
Auth::routes(['register' => false]); // Desactivar registro público

// Rutas protegidas con autenticación y filtro de congregación
Route::middleware(['auth', 'congregacion'])->group(function () {

    // Ruta principal - Dashboard (todos los usuarios)
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Panel especial para usuarios de territorios
    Route::get('/panel-territorios', [PanelTerritoriosController::class, 'index'])->name('panel-territorios');

    // =====================================================
    // RUTAS PARA TODOS LOS USUARIOS (user, admin, superadmin)
    // =====================================================

    // Ver territorios
    Route::get('territorios', [TerritorioController::class, 'index'])->name('territorios.index');
    Route::get('territorios/{territorio}', [TerritorioController::class, 'show'])->name('territorios.show')->where('territorio', '[0-9]+');

    // Ver publicadores
    Route::get('publicadores', [PublicadorController::class, 'index'])->name('publicadores.index');
    Route::get('publicadores/{publicador}', [PublicadorController::class, 'show'])->name('publicadores.show')->where('publicador', '[0-9]+');
    Route::post('publicadores/{publicador}/toggle-precursor', [PublicadorController::class, 'togglePrecursor'])->name('publicadores.togglePrecursor');
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

    // Rutas para importacion S13
    Route::get('s13/importar', [S13ImportController::class, 'index'])->name('s13.importar');
    Route::post('s13/importar/procesar', [S13ImportController::class, 'procesar'])->name('s13.importar.procesar');
    Route::post('s13/importar/confirmar', [S13ImportController::class, 'confirmar'])->name('s13.importar.confirmar');

    // Enviar WhatsApp (todos)
    Route::post('territorios/{territorio}/enviar-whatsapp', [TerritorioController::class, 'enviarWhatsapp'])->name('territorios.whatsapp')->where('territorio', '[0-9]+');

    // Perfil personal (todos)
    Route::get('perfil', [PerfilController::class, 'index'])->name('perfil.index');
    Route::post('perfil/password', [PerfilController::class, 'cambiarPassword'])->name('perfil.password');
    Route::post('perfil/nombre', [PerfilController::class, 'actualizarNombre'])->name('perfil.nombre');

    // Cambiar usuario (estilo Netflix)
    Route::get('cambiar-usuario', [CambiarUsuarioController::class, 'index'])->name('cambiar-usuario.index');
    Route::post('cambiar-usuario', [CambiarUsuarioController::class, 'cambiar'])->name('cambiar-usuario.cambiar');
    Route::get('cambiar-usuario/volver', [CambiarUsuarioController::class, 'volver'])->name('cambiar-usuario.volver');

    // =====================================================
    // RUTAS SOLO PARA ADMIN Y SUPERADMIN
    // =====================================================
    Route::middleware('role:admin')->group(function () {

        // Pagina principal de Administracion
        Route::get('administracion', function () {
            return view('administracion');
        })->name('administracion');

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


    // =====================================================
    // RUTAS PPOC - Programa de Predicacion
    // =====================================================
    Route::prefix("ppoc")->name("ppoc.")->group(function () {
        Route::get("/", [TurnoController::class, "calendario"])->name("calendario");
        Route::get("/turnos", [TurnoController::class, "index"])->name("turnos.index");
        Route::get("/turnos/create", [TurnoController::class, "create"])->name("turnos.create");
        Route::post("/turnos", [TurnoController::class, "store"])->name("turnos.store");
        Route::get("/turnos/{turno}/edit", [TurnoController::class, "edit"])->name("turnos.edit");
        Route::put("/turnos/{turno}", [TurnoController::class, "update"])->name("turnos.update");
        Route::delete("/turnos/{turno}", [TurnoController::class, "destroy"])->name("turnos.destroy");
        Route::post("/generar-mes", [TurnoController::class, "generarMes"])->name("generar-mes");
        Route::post("/asignaciones", [TurnoController::class, "asignar"])->name("asignaciones.store");
        Route::delete("/asignaciones/{asignacion}", [TurnoController::class, "desasignar"])->name("asignaciones.destroy");
        Route::delete("/turno-generado/{turnoGenerado}", [TurnoController::class, "destroyTurnoGenerado"])->name("turno-generado.destroy");
        Route::patch("/asignaciones/{asignacion}/estado", [TurnoController::class, "actualizarEstado"])->name("asignaciones.estado");
        Route::get("/aprobados", [TurnoController::class, "aprobados"])->name("aprobados");
        Route::post("/aprobados/toggle/{publicador}", [TurnoController::class, "toggleAprobado"])->name("aprobados.toggle");
    });

    // Ruta de referencia UI (desarrollo)
    Route::get('referencia-ui', function () {
        return view('referencia-ui');
    })->name('referencia-ui');
});
