<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabla para definir los turnos de predicación de cada congregación
     */
    public function up(): void
    {
        Schema::create('turnos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('congregacion_id')->constrained('congregaciones')->onDelete('cascade');
            $table->string('nombre'); // Ej: "Turno mañana", "Carrito centro"
            $table->tinyInteger('dia_semana'); // 0=Domingo, 1=Lunes, ..., 6=Sábado
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->string('ubicacion')->nullable(); // Lugar del turno
            $table->string('tipo')->default('predicacion'); // predicacion, carrito, telefonica, etc.
            $table->integer('capacidad')->default(3); // 1 capitan + 2 voluntarios = 3
            $table->text('notas')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            // Un turno es único por congregación, día y hora
            $table->unique(['congregacion_id', 'dia_semana', 'hora_inicio', 'nombre'], 'turno_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('turnos');
    }
};
