<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabla para las asignaciones mensuales de turnos a publicadores
     */
    public function up(): void
    {
        Schema::create('turno_asignaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('turno_id')->constrained('turnos')->onDelete('cascade');
            $table->foreignId('publicador_id')->constrained('publicadores')->onDelete('cascade');
            $table->date('fecha'); // Fecha especifica de la asignacion
            $table->enum('rol', ['capitan', 'voluntario'])->default('voluntario'); // Rol en el turno
            $table->enum('estado', ['pendiente', 'confirmado', 'completado', 'cancelado'])->default('pendiente');
            $table->text('notas')->nullable();
            $table->timestamps();

            // Un publicador solo puede estar asignado una vez a un turno en una fecha
            $table->unique(['turno_id', 'publicador_id', 'fecha'], 'asignacion_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('turno_asignaciones');
    }
};
