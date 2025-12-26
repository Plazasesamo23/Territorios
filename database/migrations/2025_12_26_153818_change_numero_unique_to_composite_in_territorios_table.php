<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Cambia el índice único de 'numero' a un índice compuesto 'numero + congregacion_id'
     * para permitir que cada congregación tenga su propia numeración de territorios
     */
    public function up(): void
    {
        Schema::table('territorios', function (Blueprint $table) {
            // Eliminar el índice único solo en 'numero'
            $table->dropUnique('territorios_numero_unique');

            // Crear índice único compuesto (numero + congregacion_id)
            $table->unique(['congregacion_id', 'numero'], 'territorios_congregacion_numero_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('territorios', function (Blueprint $table) {
            // Eliminar el índice compuesto
            $table->dropUnique('territorios_congregacion_numero_unique');

            // Restaurar el índice único solo en 'numero'
            $table->unique('numero', 'territorios_numero_unique');
        });
    }
};
