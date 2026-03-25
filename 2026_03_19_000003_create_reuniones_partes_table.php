<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reuniones_partes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('programa_id')->constrained('reuniones_programas')->onDelete('cascade');
            $table->enum('seccion', ['tesoros', 'maestros', 'vida_cristiana']);
            $table->string('tipo', 50);
            $table->string('titulo', 255)->nullable();
            $table->tinyInteger('duracion_minutos')->unsigned();
            $table->tinyInteger('orden')->unsigned();
            $table->foreignId('publicador_id')->nullable()->constrained('publicadores')->nullOnDelete();
            $table->foreignId('ayudante_id')->nullable()->constrained('publicadores')->nullOnDelete();
            $table->boolean('necesita_ayudante')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reuniones_partes');
    }
};
