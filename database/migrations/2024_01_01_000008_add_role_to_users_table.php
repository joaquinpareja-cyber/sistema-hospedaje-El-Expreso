<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'recepcionista', 'supervisor'])
                  ->default('recepcionista')
                  ->after('email');
            $table->boolean('activo')->default(true)->after('role');
            $table->string('telefono', 20)->nullable()->after('activo');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'activo', 'telefono']);
        });
    }
};
