<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('congregaciones', function (Blueprint $table) {
            $table->string('token_disponibilidad', 64)->nullable()->unique()->after('mensaje_whatsapp');
        });
    }

    public function down(): void
    {
        Schema::table('congregaciones', function (Blueprint $table) {
            $table->dropColumn('token_disponibilidad');
        });
    }
};
