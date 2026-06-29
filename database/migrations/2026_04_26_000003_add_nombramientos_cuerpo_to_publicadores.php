<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('publicadores', function (Blueprint $table) {
            $table->boolean('es_coordinador_cuerpo')->default(false)->after('es_siervo_ministerial');
            $table->boolean('es_secretario')->default(false)->after('es_coordinador_cuerpo');
            $table->boolean('es_sup_servicio')->default(false)->after('es_secretario');
            $table->boolean('es_sup_vym')->default(false)->after('es_sup_servicio');
            $table->boolean('es_sup_atalaya')->default(false)->after('es_sup_vym');
            $table->boolean('es_coord_mantenimiento')->default(false)->after('es_sup_atalaya');
            $table->boolean('es_consejero_auxiliar')->default(false)->after('es_coord_mantenimiento');
        });
    }

    public function down(): void
    {
        Schema::table('publicadores', function (Blueprint $table) {
            $table->dropColumn([
                'es_coordinador_cuerpo',
                'es_secretario',
                'es_sup_servicio',
                'es_sup_vym',
                'es_sup_atalaya',
                'es_coord_mantenimiento',
                'es_consejero_auxiliar',
            ]);
        });
    }
};
