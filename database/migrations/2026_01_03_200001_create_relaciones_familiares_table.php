<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('relaciones_familiares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('publicador_id')->constrained('publicadores')->onDelete('cascade');
            $table->foreignId('familiar_id')->constrained('publicadores')->onDelete('cascade');
            $table->enum('tipo_relacion', ['conyuge', 'progenitor', 'hijo']);
            $table->timestamps();

            $table->unique(['publicador_id', 'familiar_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('relaciones_familiares');
    }
};
