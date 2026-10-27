<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Dirección del personal y documentación que sube el personal de limpieza
 * (factura de luz/agua, título de bachiller, croquis del domicilio).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('direccion', 255)->nullable()->after('telefono');
        });

        Schema::create('documentos_usuario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('tipo', 30);
            $table->string('archivo');              // ruta privada en storage/app
            $table->string('nombre_original');
            $table->string('mime', 100);
            $table->unsignedInteger('tamano');      // bytes
            $table->timestamps();

            $table->unique(['user_id', 'tipo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documentos_usuario');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('direccion');
        });
    }
};
