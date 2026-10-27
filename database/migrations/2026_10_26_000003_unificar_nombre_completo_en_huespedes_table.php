<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Huéspedes: nombre + apellido pasan a una sola columna "nombre_completo".
 * Se eliminan email y dirección (no se usan en la residencial).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('huespedes', function (Blueprint $table) {
            $table->string('nombre_completo', 200)->nullable()->after('id');
        });

        DB::statement("UPDATE huespedes SET nombre_completo = TRIM(CONCAT(nombre, ' ', apellido))");
        DB::statement('ALTER TABLE huespedes MODIFY nombre_completo VARCHAR(200) NOT NULL');

        Schema::table('huespedes', function (Blueprint $table) {
            $table->dropIndex(['apellido', 'nombre']);
            $table->dropColumn(['nombre', 'apellido', 'email', 'direccion']);
            $table->index('nombre_completo');
        });
    }

    public function down(): void
    {
        Schema::table('huespedes', function (Blueprint $table) {
            $table->string('nombre', 100)->default('')->after('id');
            $table->string('apellido', 100)->default('')->after('nombre');
            $table->string('email', 120)->nullable()->after('telefono');
            $table->text('direccion')->nullable()->after('email');
        });

        // No se puede saber dónde terminaba el nombre: todo vuelve a "nombre".
        DB::statement('UPDATE huespedes SET nombre = LEFT(nombre_completo, 100)');

        Schema::table('huespedes', function (Blueprint $table) {
            $table->dropIndex(['nombre_completo']);
            $table->dropColumn('nombre_completo');
            $table->index(['apellido', 'nombre']);
        });
    }
};
