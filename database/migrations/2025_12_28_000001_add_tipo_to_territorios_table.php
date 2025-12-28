<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Añade campo tipo para diferenciar territorios normales, campaña y negocios
     */
    public function up(): void
    {
        Schema::table('territorios', function (Blueprint $table) {
            // Tipo de territorio: normal, campana, negocios
            $table->enum('tipo', ['normal', 'campana', 'negocios'])->default('normal')->after('numero');

            // Modificar el índice único para incluir el tipo
            // Ahora puede haber: 1 (normal), C-1 (campaña), N-1 (negocios) en la misma congregación
        });

        // Actualizar el índice único para que sea congregacion_id + tipo + numero
        Schema::table('territorios', function (Blueprint $table) {
            // Primero eliminar el índice existente si existe
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $indexes = $sm->listTableIndexes('territorios');

            if (isset($indexes['territorios_congregacion_id_numero_unique'])) {
                $table->dropUnique('territorios_congregacion_id_numero_unique');
            }

            // Crear nuevo índice único que incluya el tipo
            $table->unique(['congregacion_id', 'tipo', 'numero'], 'territorios_congregacion_tipo_numero_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('territorios', function (Blueprint $table) {
            $table->dropUnique('territorios_congregacion_tipo_numero_unique');
            $table->dropColumn('tipo');

            // Restaurar índice original
            $table->unique(['congregacion_id', 'numero'], 'territorios_congregacion_id_numero_unique');
        });
    }
};
