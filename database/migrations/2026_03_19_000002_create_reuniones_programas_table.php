<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reuniones_programas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('congregacion_id')->constrained('congregaciones')->onDelete('cascade');
            $table->date('fecha_semana');
            $table->foreignId('presidente_id')->nullable()->constrained('publicadores')->nullOnDelete();
            $table->foreignId('oracion_inicio_id')->nullable()->constrained('publicadores')->nullOnDelete();
            $table->foreignId('oracion_final_id')->nullable()->constrained('publicadores')->nullOnDelete();
            $table->foreignId('conductor_estudio_id')->nullable()->constrained('publicadores')->nullOnDelete();
            $table->foreignId('lector_estudio_id')->nullable()->constrained('publicadores')->nullOnDelete();
            $table->enum('estado', ['borrador', 'publicado'])->default('borrador');
            $table->text('notas')->nullable();
            $table->timestamps();

            $table->unique(['congregacion_id', 'fecha_semana']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reuniones_programas');
    }
};
