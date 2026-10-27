<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cargos_adicionales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reserva_id')->constrained('reservas')->restrictOnDelete();
            $table->foreignId('factura_id')->nullable()->constrained('facturas')->nullOnDelete();
            $table->string('concepto', 150);
            $table->enum('categoria', [
                'restaurante',
                'minibar',
                'lavanderia',
                'telefono',
                'transporte',
                'tours',
                'spa',
                'otros'
            ])->default('otros');
            $table->decimal('precio_unitario', 10, 2);
            $table->unsignedSmallInteger('cantidad')->default(1);
            $table->decimal('subtotal', 10, 2);
            $table->date('fecha');
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cargos_adicionales');
    }
};
