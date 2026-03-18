<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('territorios', 'tipo')) {
            Schema::table('territorios', function (Blueprint $table) {
                $table->enum('tipo', ['normal', 'campana', 'negocios'])->default('normal')->after('numero');
            });
        }

        // Drop old unique index via raw SQL if it exists
        $indexes = DB::select("SHOW INDEX FROM territorios WHERE Key_name = 'territorios_congregacion_id_numero_unique'");
        if (!empty($indexes)) {
            DB::statement("ALTER TABLE territorios DROP INDEX territorios_congregacion_id_numero_unique");
        }

        // Add new unique index if not exists
        $newIndexes = DB::select("SHOW INDEX FROM territorios WHERE Key_name = 'territorios_congregacion_tipo_numero_unique'");
        if (empty($newIndexes)) {
            Schema::table('territorios', function (Blueprint $table) {
                $table->unique(['congregacion_id', 'tipo', 'numero'], 'territorios_congregacion_tipo_numero_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::table('territorios', function (Blueprint $table) {
            $table->dropUnique('territorios_congregacion_tipo_numero_unique');
            $table->dropColumn('tipo');
            $table->unique(['congregacion_id', 'numero'], 'territorios_congregacion_id_numero_unique');
        });
    }
};
