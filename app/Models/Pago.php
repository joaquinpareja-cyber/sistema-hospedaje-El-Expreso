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

    /** Métodos de pago aceptados (clave => etiqueta) */
    public const METODOS = [
        'efectivo'        => 'Efectivo',
        'tarjeta_credito' => 'Tarjeta de crédito',
        'tarjeta_debito'  => 'Tarjeta de débito',
        'transferencia'   => 'Transferencia bancaria',
        'qr'              => 'Pago QR',
        'otro'            => 'Otro',
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

    public function getMetodoLabelAttribute(): string
    {
        return self::METODOS[$this->metodo_pago] ?? ucfirst(str_replace('_', ' ', $this->metodo_pago));
    }

    public function getMetodoIconoAttribute(): string
    {
        return match ($this->metodo_pago) {
            'efectivo'        => 'fas fa-money-bill-wave',
            'tarjeta_credito' => 'fas fa-credit-card',
            'tarjeta_debito'  => 'fas fa-credit-card',
            'transferencia'   => 'fas fa-exchange-alt',
            'qr'              => 'fas fa-qrcode',
            default           => 'fas fa-coins',
        };
    }
}
