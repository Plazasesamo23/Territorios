<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disponibilidad_ppoc', function (Blueprint $table) {
            $table->id();
            $table->foreignId('publicador_id')->constrained('publicadores')->onDelete('cascade');
            $table->foreignId('turno_ppoc_id')->constrained('turnos_ppoc')->onDelete('cascade');
            $table->timestamps();

            // Un publicador solo puede tener una entrada por turno
            $table->unique(['publicador_id', 'turno_ppoc_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disponibilidad_ppoc');
    }
};
