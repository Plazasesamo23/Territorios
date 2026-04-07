<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reuniones_historial', function (Blueprint $table) {
            $table->boolean('es_emergencia')->default(false)->after('rol');
        });
    }

    public function down(): void
    {
        Schema::table('reuniones_historial', function (Blueprint $table) {
            $table->dropColumn('es_emergencia');
        });
    }
};
