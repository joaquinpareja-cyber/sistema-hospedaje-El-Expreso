<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facturas', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 20)->unique();           // FAC-2024-0001
            $table->foreignId('reserva_id')->constrained('reservas')->restrictOnDelete();
            $table->foreignId('huesped_id')->constrained('huespedes')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->date('fecha_emision');
            $table->decimal('subtotal', 10, 2);
            $table->decimal('igv', 10, 2)->default(0);        // 18% IGV
            $table->decimal('descuento', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->enum('estado', ['pendiente', 'pagada', 'anulada'])->default('pendiente');
            $table->enum('tipo_comprobante', ['boleta', 'factura', 'recibo'])->default('boleta');
            $table->string('ruc_cliente', 11)->nullable();    // Para facturas empresariales
            $table->string('razon_social', 150)->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facturas');
    }
};
