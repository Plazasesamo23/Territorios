<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('publicadores', function (Blueprint $table) {
            $table->boolean('es_anciano')->default(false)->after('es_precursor');
            $table->boolean('es_siervo_ministerial')->default(false)->after('es_anciano');
        });
    }

    public function down(): void
    {
        Schema::table('publicadores', function (Blueprint $table) {
            $table->dropColumn(['es_anciano', 'es_siervo_ministerial']);
        });
    }
};
