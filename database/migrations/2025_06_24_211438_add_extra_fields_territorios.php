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
        Schema::table('territorios', function (Blueprint $table) {
            $table->text('descripcion')->nullable()->after('nombre');
            $table->decimal('coordenadas_lat', 10, 8)->nullable()->after('descripcion');
            $table->decimal('coordenadas_lng', 11, 8)->nullable()->after('coordenadas_lat');
            $table->boolean('activo')->default(true)->after('estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('territorios', function (Blueprint $table) {
            $table->dropColumn(['descripcion', 'coordenadas_lat', 'coordenadas_lng', 'activo']);
        });
    }
};
