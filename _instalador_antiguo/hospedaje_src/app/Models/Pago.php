<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pago extends Model
{
    protected $table = 'pagos';

    protected $fillable = [
        'factura_id',
        'user_id',
        'monto',
        'metodo_pago',
        'referencia',
        'fecha_pago',
        'observaciones',
    ];

    protected $casts = [
        'fecha_pago' => 'date',
        'monto'      => 'decimal:2',
    ];

    /* ---- Relaciones ---- */

    public function factura(): BelongsTo
    {
        return $this->belongsTo(Factura::class, 'factura_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /* ---- Helpers ---- */

    public function getMetodoIconoAttribute(): string
    {
        return match ($this->metodo_pago) {
            'efectivo'        => 'fas fa-money-bill-wave',
            'tarjeta_credito' => 'fas fa-credit-card',
            'tarjeta_debito'  => 'fas fa-credit-card',
            'transferencia'   => 'fas fa-exchange-alt',
            'yape'            => 'fas fa-mobile-alt',
            'plin'            => 'fas fa-mobile-alt',
            default           => 'fas fa-coins',
        };
    }
}
