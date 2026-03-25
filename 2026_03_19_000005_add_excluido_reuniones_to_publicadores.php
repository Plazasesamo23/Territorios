<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('publicadores', function (Blueprint $table) {
            $table->boolean('excluido_reuniones')->default(false)->after('genero');
        });
    }

    public function down(): void
    {
        Schema::table('publicadores', function (Blueprint $table) {
            $table->dropColumn('excluido_reuniones');
        });
    }
};
