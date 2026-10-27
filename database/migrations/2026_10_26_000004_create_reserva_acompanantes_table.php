<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Personas que se alojan junto al titular de la reserva (habitaciones dobles, triples...).
 * Cada acompañante es un huésped con su ficha completa.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reserva_acompanantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reserva_id')->constrained('reservas')->cascadeOnDelete();
            $table->foreignId('huesped_id')->constrained('huespedes')->restrictOnDelete();
            $table->timestamps();

            $table->unique(['reserva_id', 'huesped_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reserva_acompanantes');
    }
};
