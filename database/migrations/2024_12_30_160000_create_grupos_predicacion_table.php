<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grupos_predicacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('congregacion_id')->constrained('congregaciones')->onDelete('cascade');
            $table->integer('numero');
            $table->string('nombre')->nullable();
            $table->timestamps();
            
            $table->unique(['congregacion_id', 'numero']);
        });

        Schema::table('publicadores', function (Blueprint $table) {
            $table->foreignId('grupo_predicacion_id')->nullable()->after('congregacion_id')->constrained('grupos_predicacion')->onDelete('set null');
            $table->boolean('es_superintendente')->default(false)->after('es_precursor');
            $table->boolean('es_auxiliar')->default(false)->after('es_superintendente');
        });
    }

    public function down(): void
    {
        Schema::table('publicadores', function (Blueprint $table) {
            $table->dropForeign(['grupo_predicacion_id']);
            $table->dropColumn(['grupo_predicacion_id', 'es_superintendente', 'es_auxiliar']);
        });

        Schema::dropIfExists('grupos_predicacion');
    }
};
