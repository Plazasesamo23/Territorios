<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reuniones_historial', function (Blueprint $table) {
            $table->id();
            $table->foreignId('congregacion_id')->constrained('congregaciones')->onDelete('cascade');
            $table->foreignId('publicador_id')->constrained('publicadores')->onDelete('cascade');
            $table->foreignId('programa_id')->constrained('reuniones_programas')->onDelete('cascade');
            $table->date('fecha_semana');
            $table->string('tipo_parte', 50);
            $table->enum('rol', ['principal', 'ayudante']);
            $table->timestamp('created_at')->useCurrent();

            $table->index(['congregacion_id', 'publicador_id', 'fecha_semana'], 'rh_cong_pub_fecha_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reuniones_historial');
    }
};
