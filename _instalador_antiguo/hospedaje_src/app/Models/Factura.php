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
        'igv',
        'descuento',
        'total',
        'estado',
        'tipo_comprobante',
        'ruc_cliente',
        'razon_social',
        'observaciones',
    ];

    protected $casts = [
        'fecha_emision' => 'date',
        'subtotal'      => 'decimal:2',
        'igv'           => 'decimal:2',
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

    public function getEstadoBadgeAttribute(): string
    {
        return match ($this->estado) {
            'pendiente' => 'warning',
            'pagada'    => 'success',
            'anulada'   => 'danger',
            default     => 'secondary',
        };
    }

    public function getMontoPagadoAttribute(): float
    {
        return $this->pagos()->sum('monto');
    }

    public function getSaldoPendienteAttribute(): float
    {
        return $this->total - $this->monto_pagado;
    }

    public static function generarNumero(): string
    {
        $año = date('Y');
        $ultimo = static::whereYear('created_at', $año)->max('id') ?? 0;
        return "FAC-{$año}-" . str_pad($ultimo + 1, 4, '0', STR_PAD_LEFT);
    }
}
