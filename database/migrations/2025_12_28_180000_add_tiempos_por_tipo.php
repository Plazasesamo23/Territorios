<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Añade campos de tiempo por tipo de territorio
     */
    public function up(): void
    {
        Schema::table('congregaciones', function (Blueprint $table) {
            // Tiempos para territorios de Campaña
            $table->integer('dias_limite_activo_campana')->default(30)->after('dias_archivo');
            $table->integer('dias_archivo_campana')->default(30)->after('dias_limite_activo_campana');

            // Tiempos para territorios de Negocios
            $table->integer('dias_limite_activo_negocios')->default(60)->after('dias_archivo_campana');
            $table->integer('dias_archivo_negocios')->default(60)->after('dias_limite_activo_negocios');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('congregaciones', function (Blueprint $table) {
            $table->dropColumn([
                'dias_limite_activo_campana',
                'dias_archivo_campana',
                'dias_limite_activo_negocios',
                'dias_archivo_negocios'
            ]);
        });
    }
};
