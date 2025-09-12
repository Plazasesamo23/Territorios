<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('territorios', function (Blueprint $table) {
            $table->id();
            $table->integer('numero')->unique();
            $table->string('imagen_url')->nullable();
            $table->enum('estado', ['libre', 'activo', 'archivo', 'pendiente', 'atrasado'])->default('libre');
            $table->text('notas')->nullable();
            $table->date('ultima_salida')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('territorios');
    }
};
