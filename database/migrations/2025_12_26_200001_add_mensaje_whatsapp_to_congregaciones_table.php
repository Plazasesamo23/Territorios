<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Agrega campo para personalizar el mensaje de WhatsApp por congregación
     */
    public function up(): void
    {
        Schema::table('congregaciones', function (Blueprint $table) {
            $table->text('mensaje_whatsapp')->nullable()->after('dias_archivo');
        });

        // Establecer mensaje por defecto para congregaciones existentes
        $mensajeDefault = "Hola {nombre}, te envío el territorio {numero}.\n\nImagen del territorio:\n{imagen_url}";

        DB::table('congregaciones')->update(['mensaje_whatsapp' => $mensajeDefault]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('congregaciones', function (Blueprint $table) {
            $table->dropColumn('mensaje_whatsapp');
        });
    }
};
