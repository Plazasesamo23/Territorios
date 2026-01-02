<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('turno_asignaciones', function (Blueprint $table) {
            // Agregar referencia a turno_generado
            $table->foreignId('turno_generado_id')->nullable()->after('turno_id')
                  ->constrained('turnos_generados')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('turno_asignaciones', function (Blueprint $table) {
            $table->dropForeign(['turno_generado_id']);
            $table->dropColumn('turno_generado_id');
        });
    }
};