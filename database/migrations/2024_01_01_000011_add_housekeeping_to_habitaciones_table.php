<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('habitaciones', function (Blueprint $table) {
            $table->enum('estado_limpieza', ['limpia', 'en_limpieza', 'sucia', 'inspeccion'])
                  ->default('limpia')->after('estado');
            $table->timestamp('limpieza_actualizado')->nullable()->after('estado_limpieza');
            $table->text('limpieza_notas')->nullable()->after('limpieza_actualizado');
            $table->unsignedBigInteger('limpieza_user_id')->nullable()->after('limpieza_notas');
        });
    }

    public function down(): void
    {
        Schema::table('habitaciones', function (Blueprint $table) {
            $table->dropColumn(['estado_limpieza', 'limpieza_actualizado', 'limpieza_notas', 'limpieza_user_id']);
        });
    }
};
