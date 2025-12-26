<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('congregaciones', function (Blueprint $table) {
            $table->integer('dias_limite_activo')->default(60)->after('password');
            $table->integer('dias_archivo')->default(90)->after('dias_limite_activo');
        });
    }

    public function down(): void
    {
        Schema::table('congregaciones', function (Blueprint $table) {
            $table->dropColumn(['dias_limite_activo', 'dias_archivo']);
        });
    }
};
