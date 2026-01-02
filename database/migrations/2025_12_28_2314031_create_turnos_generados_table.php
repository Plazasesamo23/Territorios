<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('turnos_generados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('turno_id')->constrained('turnos')->onDelete('cascade');
            $table->foreignId('congregacion_id')->constrained('congregaciones')->onDelete('cascade');
            $table->date('fecha');
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->integer('capacidad')->default(3);
            $table->string('ubicacion')->nullable();
            $table->text('notas')->nullable();
            $table->enum('estado', ['abierto', 'completo', 'cancelado'])->default('abierto');
            $table->timestamps();

            // Un turno solo puede existir una vez por fecha
            $table->unique(['turno_id', 'fecha'], 'turno_generado_unique');
            $table->index(['congregacion_id', 'fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('turnos_generados');
    }
};