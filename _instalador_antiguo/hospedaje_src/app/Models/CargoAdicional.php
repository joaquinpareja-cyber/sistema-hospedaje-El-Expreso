<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CargoAdicional extends Model
{
    protected $table = 'cargos_adicionales';

    protected $fillable = [
        'reserva_id',
        'factura_id',
        'concepto',
        'categoria',
        'precio_unitario',
        'cantidad',
        'subtotal',
        'fecha',
        'observaciones',
    ];

    protected $casts = [
        'fecha'           => 'date',
        'precio_unitario' => 'decimal:2',
        'subtotal'        => 'decimal:2',
    ];

    /* ---- Relaciones ---- */

    public function reserva(): BelongsTo
    {
        return $this->belongsTo(Reserva::class, 'reserva_id');
    }

    public function factura(): BelongsTo
    {
        return $this->belongsTo(Factura::class, 'factura_id');
    }

    /* ---- Hooks ---- */

    protected static function boot(): void
    {
        parent::boot();

        static::saving(function ($cargo) {
            $cargo->subtotal = $cargo->precio_unitario * $cargo->cantidad;
        });
    }
}
