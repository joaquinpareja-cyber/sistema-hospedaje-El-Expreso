<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique();           // Ej: RES-2024-0001
            $table->foreignId('huesped_id')->constrained('huespedes')->restrictOnDelete();
            $table->foreignId('habitacion_id')->constrained('habitaciones')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete(); // recepcionista
            $table->date('fecha_entrada');
            $table->date('fecha_salida');
            $table->date('fecha_checkin')->nullable();
            $table->date('fecha_checkout')->nullable();
            $table->unsignedSmallInteger('num_personas')->default(1);
            $table->enum('estado', [
                'pendiente',
                'confirmada',
                'checkin',
                'checkout',
                'cancelada',
                'no_show'
            ])->default('pendiente');
            $table->decimal('precio_noche', 10, 2);
            $table->unsignedSmallInteger('num_noches');
            $table->decimal('subtotal', 10, 2);
            $table->decimal('descuento', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->enum('origen', ['web', 'telefono', 'presencial', 'agencia'])->default('presencial');
            $table->text('observaciones')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservas');
    }
};
