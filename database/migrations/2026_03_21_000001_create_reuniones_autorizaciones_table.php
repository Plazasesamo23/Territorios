<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reuniones_autorizaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('publicador_id')->constrained('publicadores')->onDelete('cascade');
            $table->foreignId('congregacion_id')->constrained('congregaciones')->onDelete('cascade');
            $table->string('tipo_parte', 50);
            $table->timestamps();

            $table->unique(['publicador_id', 'tipo_parte'], 'pub_tipo_unique');
            $table->index(['congregacion_id', 'tipo_parte']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reuniones_autorizaciones');
    }
};
