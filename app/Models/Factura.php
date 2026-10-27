<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Factura extends Model
{
    protected $table = 'facturas';

    protected $fillable = [
        'numero',
        'reserva_id',
        'huesped_id',
        'user_id',
        'fecha_emision',
        'subtotal',
        'iva',
        'descuento',
        'total',
        'estado',
        'tipo_comprobante',
        'nit_cliente',
        'razon_social',
        'observaciones',
    ];

    protected $casts = [
        'fecha_emision' => 'date',
        'subtotal'      => 'decimal:2',
        'iva'           => 'decimal:2',
        'descuento'     => 'decimal:2',
        'total'         => 'decimal:2',
    ];

    /* ---- Relaciones ---- */

    public function reserva(): BelongsTo
    {
        return $this->belongsTo(Reserva::class, 'reserva_id');
    }

    public function huesped(): BelongsTo
    {
        return $this->belongsTo(Huesped::class, 'huesped_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class, 'factura_id');
    }

    /* ---- Helpers ---- */

    /**
     * Emite el comprobante de una reserva: estancia + cargos adicionales - descuento (+ IVA).
     * $datos: tipo_comprobante y, opcionalmente, nit_cliente, razon_social, observaciones.
     * Debe llamarse dentro de una transacción, con la reserva bloqueada.
     */
    public static function emitirParaReserva(Reserva $reserva, array $datos, float $ivaPorcentaje = 0): self
    {
        // subtotal (bruto) - descuento = base imponible; + IVA = total
        $subtotal  = round((float) $reserva->subtotal + (float) $reserva->cargosAdicionales()->sum('subtotal'), 2);
        $descuento = round((float) $reserva->descuento, 2);
        $base      = $subtotal - $descuento;
        $iva       = round($base * $ivaPorcentaje / 100, 2);

        $factura = static::create([
            'numero'          => static::generarNumero($datos['tipo_comprobante']),
            'reserva_id'      => $reserva->id,
            'huesped_id'      => $reserva->huesped_id,
            'user_id'         => auth()->id(),
            'fecha_emision'   => now(),
            'subtotal'        => $subtotal,
            'iva'             => $iva,
            'descuento'       => $descuento,
            'total'           => round($base + $iva, 2),
            'estado'          => 'pendiente',
            'tipo_comprobante'=> $datos['tipo_comprobante'],
            'nit_cliente'     => $datos['nit_cliente'] ?? null,
            'razon_social'    => $datos['razon_social'] ?? null,
            'observaciones'   => $datos['observaciones'] ?? null,
        ]);

        $reserva->cargosAdicionales()->update(['factura_id' => $factura->id]);

        return $factura;
    }

    public function getEstadoBadgeAttribute(): string
    {
        return match ($this->estado) {
            'pendiente' => 'warning',
            'pagada'    => 'success',
            'anulada'   => 'danger',
            default     => 'secondary',
        };
    }

    /**
     * Usa withSum('pagos', 'monto') o la relación ya cargada si existen,
     * para no lanzar una consulta por cada fila en los listados.
     */
    public function getMontoPagadoAttribute(): float
    {
        if (array_key_exists('pagos_sum_monto', $this->attributes)) {
            return (float) $this->attributes['pagos_sum_monto'];
        }
        if ($this->relationLoaded('pagos')) {
            return (float) $this->pagos->sum('monto');
        }
        return (float) $this->pagos()->sum('monto');
    }

    /** % de IVA con que se emitió (se deduce de los montos, no de la configuración actual) */
    public function getIvaPorcentajeAttribute(): float
    {
        $base = (float) $this->subtotal - (float) $this->descuento;
        return $base > 0 ? round((float) $this->iva / $base * 100, 2) : 0;
    }

    public function getSaldoPendienteAttribute(): float
    {
        return round((float) $this->total - $this->monto_pagado, 2);
    }

    /**
     * Siguiente número correlativo de la serie configurada para el tipo de
     * comprobante (B001-00000001, F001-00000001, R001-00000001).
     * Debe llamarse dentro de una transacción.
     */
    public static function generarNumero(string $tipoComprobante): string
    {
        $defaults = ['boleta' => 'B001', 'factura' => 'F001', 'recibo' => 'R001'];
        $serie    = Configuracion::get("facturacion_serie_{$tipoComprobante}")
                    ?: ($defaults[$tipoComprobante] ?? 'R001');
        $prefijo  = strtoupper(trim($serie)) . '-';

        $ultimo = static::where('numero', 'like', $prefijo . '%')
            ->lockForUpdate()
            ->pluck('numero')
            ->map(fn($n) => (int) substr($n, strlen($prefijo)))
            ->max() ?? 0;

        return $prefijo . str_pad($ultimo + 1, 8, '0', STR_PAD_LEFT);
    }
}
