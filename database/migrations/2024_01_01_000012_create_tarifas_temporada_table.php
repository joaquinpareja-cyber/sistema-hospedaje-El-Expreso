<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tarifas_temporada', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);                    // "Temporada Alta", "Feriados", etc.
            $table->foreignId('tipo_habitacion_id')
                  ->nullable()
                  ->constrained('tipo_habitaciones')
                  ->nullOnDelete();                           // NULL = aplica a todos los tipos
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->decimal('precio_noche', 10, 2);
            $table->enum('tipo_precio', ['fijo', 'porcentaje'])->default('fijo');
            // Si tipo_precio=porcentaje, precio_noche es el % de incremento sobre precio base
            $table->text('descripcion')->nullable();
            $table->boolean('activa')->default(true);
            $table->unsignedTinyInteger('prioridad')->default(0); // mayor = más prioridad
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tarifas_temporada');
    }
};
