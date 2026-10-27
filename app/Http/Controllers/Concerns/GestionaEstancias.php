<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Habitacion;
use App\Models\TarifaTemporada;
use Illuminate\Validation\ValidationException;

/** Validación de habitación y cálculo de precios, compartido por reservas y registro directo. */
trait GestionaEstancias
{
    protected function validarHabitacion(Habitacion $habitacion, array $data, ?int $excluirReservaId = null): void
    {
        if (!$habitacion->activa) {
            throw ValidationException::withMessages(['habitacion_id' => 'La habitación seleccionada está inactiva.']);
        }

        $capacidad = $habitacion->tipoHabitacion->capacidad;
        if ($data['num_personas'] > $capacidad) {
            throw ValidationException::withMessages([
                'num_personas' => "La habitación {$habitacion->numero} admite como máximo {$capacidad} persona(s).",
            ]);
        }

        if (!$habitacion->estaDisponible($data['fecha_entrada'], $data['fecha_salida'], $excluirReservaId)) {
            throw ValidationException::withMessages([
                'habitacion_id' => 'La habitación no está disponible para las fechas seleccionadas.',
            ]);
        }
    }

    /** Precio de la estancia según tarifas de temporada, con el descuento aplicado. */
    protected function calcularMontos(Habitacion $habitacion, array $data): array
    {
        $estancia = TarifaTemporada::calcularEstancia(
            $habitacion->tipo_habitacion_id,
            $data['fecha_entrada'],
            $data['fecha_salida'],
            (float) $habitacion->tipoHabitacion->precio_base
        );

        return array_merge(
            ['precio_noche' => $estancia['precio_noche'], 'num_noches' => $estancia['num_noches']],
            $this->aplicarDescuento($estancia['subtotal'], $data)
        );
    }

    protected function aplicarDescuento(float $subtotal, array $data): array
    {
        $descuento = round((float) ($data['descuento'] ?? 0), 2);
        if ($descuento > $subtotal) {
            throw ValidationException::withMessages([
                'descuento' => 'El descuento no puede ser mayor que el subtotal (' . number_format($subtotal, 2) . ').',
            ]);
        }

        return [
            'subtotal'  => $subtotal,
            'descuento' => $descuento,
            'total'     => round($subtotal - $descuento, 2),
        ];
    }
}
