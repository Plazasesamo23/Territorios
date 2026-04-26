<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tareas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('congregacion_id')->constrained('congregaciones')->onDelete('cascade');
            $table->string('titulo');
            $table->text('descripcion')->nullable();
            $table->enum('departamento', ['territorios', 'ppoc', 'reuniones', 'administracion', 'general']);
            $table->foreignId('asignado_a')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('creado_por')->constrained('users')->onDelete('cascade');
            $table->enum('visibilidad', ['publica', 'personal'])->default('publica');
            $table->enum('estado', ['pendiente', 'en_curso', 'bloqueada', 'hecha'])->default('pendiente');
            $table->enum('prioridad', ['baja', 'media', 'alta'])->default('media');
            $table->date('fecha_limite')->nullable();
            $table->integer('orden')->default(0);
            $table->timestamp('completada_at')->nullable();
            $table->foreignId('completada_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['congregacion_id', 'estado']);
            $table->index(['congregacion_id', 'departamento', 'estado']);
            $table->index(['asignado_a', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tareas');
    }
};
