<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('habitaciones', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 10)->unique();        // Nro de habitación: 101, 202A…
            $table->string('piso', 10)->nullable();
            $table->foreignId('tipo_habitacion_id')->constrained('tipo_habitaciones')->restrictOnDelete();
            $table->enum('estado', ['disponible', 'ocupada', 'mantenimiento', 'reservada'])->default('disponible');
            $table->text('descripcion')->nullable();
            $table->string('imagen')->nullable();          // Ruta de imagen
            $table->boolean('activa')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('habitaciones');
    }
};
