<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('publicadores', function (Blueprint $table) {
            // Titulares (unico por categoria a nivel de congregacion, controlado en controller)
            $table->boolean('es_siervo_cuentas')->default(false)->after('es_consejero_auxiliar');
            $table->boolean('es_siervo_territorios')->default(false)->after('es_siervo_cuentas');
            $table->boolean('es_siervo_multimedia')->default(false)->after('es_siervo_territorios');
            $table->boolean('es_siervo_limpieza')->default(false)->after('es_siervo_multimedia');
            $table->boolean('es_siervo_publicaciones')->default(false)->after('es_siervo_limpieza');
            // Auxiliares (varios permitidos por categoria)
            $table->boolean('es_aux_cuentas')->default(false)->after('es_siervo_publicaciones');
            $table->boolean('es_aux_territorios')->default(false)->after('es_aux_cuentas');
            $table->boolean('es_aux_multimedia')->default(false)->after('es_aux_territorios');
            $table->boolean('es_aux_limpieza')->default(false)->after('es_aux_multimedia');
            $table->boolean('es_aux_publicaciones')->default(false)->after('es_aux_limpieza');
        });
    }

    public function down(): void
    {
        Schema::table('publicadores', function (Blueprint $table) {
            $table->dropColumn([
                'es_siervo_cuentas',
                'es_siervo_territorios',
                'es_siervo_multimedia',
                'es_siervo_limpieza',
                'es_siervo_publicaciones',
                'es_aux_cuentas',
                'es_aux_territorios',
                'es_aux_multimedia',
                'es_aux_limpieza',
                'es_aux_publicaciones',
            ]);
        });
    }
};
