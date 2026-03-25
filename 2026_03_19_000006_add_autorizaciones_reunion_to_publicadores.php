<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('publicadores', function (Blueprint $table) {
            $table->boolean('puede_dirigir_estudio')->default(false)->after('excluido_reuniones');
            $table->boolean('puede_leer_estudio')->default(false)->after('puede_dirigir_estudio');
        });

        // Por defecto, todos los ancianos pueden dirigir y leer
        \DB::table('publicadores')->where('es_anciano', true)->update([
            'puede_dirigir_estudio' => true,
            'puede_leer_estudio' => true,
        ]);
    }

    public function down(): void
    {
        Schema::table('publicadores', function (Blueprint $table) {
            $table->dropColumn(['puede_dirigir_estudio', 'puede_leer_estudio']);
        });
    }
};
