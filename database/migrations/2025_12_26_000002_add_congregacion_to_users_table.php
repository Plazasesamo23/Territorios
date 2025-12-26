<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('congregacion_id')
                ->nullable()
                ->after('id')
                ->constrained('congregaciones')
                ->onDelete('set null');

            $table->enum('role', ['user', 'admin', 'superadmin'])
                ->default('user')
                ->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['congregacion_id']);
            $table->dropColumn(['congregacion_id', 'role']);
        });
    }
};
