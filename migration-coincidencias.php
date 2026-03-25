<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coincidencias_historico', function (Blueprint $table) {
            $table->id();
            $table->foreignId('congregacion_id')->constrained('congregaciones')->onDelete('cascade');
            $table->unsignedBigInteger('publicador_1_id');
            $table->unsignedBigInteger('publicador_2_id');
            $table->integer('cantidad')->default(1); // veces que coincidieron
            $table->string('ultimo_ano', 10)->nullable(); // último año que coincidieron
            $table->timestamps();

            $table->foreign('publicador_1_id')->references('id')->on('publicadores')->onDelete('cascade');
            $table->foreign('publicador_2_id')->references('id')->on('publicadores')->onDelete('cascade');

            $table->unique(['congregacion_id', 'publicador_1_id', 'publicador_2_id'], 'unique_pair');
            $table->index('publicador_1_id', 'idx_pub1');
            $table->index('publicador_2_id', 'idx_pub2');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coincidencias_historico');
    }
};
