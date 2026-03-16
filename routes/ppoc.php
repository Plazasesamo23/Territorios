<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PPOC\TurnoController;
use App\Http\Controllers\PPOC\DisponibilidadPpocController;

// =====================================================
// RUTAS PUBLICAS - Disponibilidad PPOC (sin auth)
// =====================================================
Route::get('/disponibilidad/{token}', [DisponibilidadPpocController::class, 'form'])->name('disponibilidad.form');
Route::post('/disponibilidad/{token}', [DisponibilidadPpocController::class, 'store'])->name('disponibilidad.store');
Route::get('/disponibilidad/{token}/publicador/{publicador}', [DisponibilidadPpocController::class, 'getDisponibilidad'])->name('disponibilidad.get');

// =====================================================
// RUTAS PPOC AUTENTICADAS
// =====================================================
Route::middleware(['auth', 'congregacion'])->group(function () {

    // PPOC - Accesibles para usuarios PPOC y admins
    Route::prefix("ppoc")->name("ppoc.")->group(function () {
        Route::get("/", [TurnoController::class, "calendario"])->name("calendario");
        Route::post("/generar-mes", [TurnoController::class, "generarMes"])->name("generar-mes");
        Route::post("/limpiar-mes", [TurnoController::class, "limpiarMes"])->name("limpiar-mes");
        Route::post("/asignaciones", [TurnoController::class, "asignar"])->name("asignaciones.store");
        Route::delete("/asignaciones/{asignacion}", [TurnoController::class, "desasignar"])->name("asignaciones.destroy");
        Route::patch("/asignaciones/{asignacion}/estado", [TurnoController::class, "actualizarEstado"])->name("asignaciones.estado");
        Route::post("/asignacion-automatica", [TurnoController::class, "asignacionAutomatica"])->name("asignacion-automatica");
        Route::get("/asignaciones/{asignacion}/sugerencias", [TurnoController::class, "getSugerencias"])->name("asignaciones.sugerencias");
        Route::post("/asignaciones/{asignacion}/reemplazar", [TurnoController::class, "reemplazar"])->name("asignaciones.reemplazar");
        Route::get("/exportar-pdf", [TurnoController::class, "exportarPdf"])->name("exportar-pdf");
    });

    // RUTAS PPOC SOLO ADMIN
    Route::middleware("role:admin")->prefix("ppoc")->name("ppoc.")->group(function () {
        Route::get("/turnos", [TurnoController::class, "index"])->name("turnos.index");
        Route::get("/turnos/create", [TurnoController::class, "create"])->name("turnos.create");
        Route::post("/turnos", [TurnoController::class, "store"])->name("turnos.store");
        Route::get("/turnos/{turno}/edit", [TurnoController::class, "edit"])->name("turnos.edit");
        Route::put("/turnos/{turno}", [TurnoController::class, "update"])->name("turnos.update");
        Route::delete("/turnos/{turno}", [TurnoController::class, "destroy"])->name("turnos.destroy");
        Route::delete("/turno-generado/{turnoGenerado}", [TurnoController::class, "destroyTurnoGenerado"])->name("turno-generado.destroy");
        Route::get("/aprobados", [TurnoController::class, "aprobados"])->name("aprobados");
        Route::post("/aprobados/toggle/{publicador}", [TurnoController::class, "toggleAprobado"])->name("aprobados.toggle");
        Route::post("/capitanes/toggle/{publicador}", [TurnoController::class, "toggleCapitan"])->name("capitanes.toggle");

        // Disponibilidad PPOC - Vistas admin
        Route::get("/disponibilidad/por-turno", [DisponibilidadPpocController::class, 'porTurno'])->name('disponibilidad.por-turno');
        Route::get("/disponibilidad/por-publicador", [DisponibilidadPpocController::class, 'porPublicador'])->name('disponibilidad.por-publicador');
    });
});
