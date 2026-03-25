<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grupos_historico', function (Blueprint $table) {
            $table->id();
            $table->foreignId('congregacion_id')->constrained('congregaciones')->onDelete('cascade');
            $table->string('ano_servicio', 10); // "2024/2025"
            $table->integer('grupo_numero'); // 1-6
            $table->foreignId('publicador_id')->constrained('publicadores')->onDelete('cascade');
            $table->string('rol', 20)->default('normal'); // superintendente/auxiliar/precursor/normal
            $table->timestamp('created_at')->useCurrent();

            $table->index(['ano_servicio', 'congregacion_id'], 'idx_ano_cong');
            $table->index('publicador_id', 'idx_publicador');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grupos_historico');
    }
};
