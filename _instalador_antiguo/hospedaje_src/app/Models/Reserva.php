<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reserva extends Model
{
    use SoftDeletes;

    protected $table = 'reservas';

    protected $fillable = [
        'codigo',
        'huesped_id',
        'habitacion_id',
        'user_id',
        'fecha_entrada',
        'fecha_salida',
        'fecha_checkin',
        'fecha_checkout',
        'num_personas',
        'estado',
        'precio_noche',
        'num_noches',
        'subtotal',
        'descuento',
        'total',
        'origen',
        'observaciones',
    ];

    protected $casts = [
        'fecha_entrada'  => 'date',
        'fecha_salida'   => 'date',
        'fecha_checkin'  => 'date',
        'fecha_checkout' => 'date',
        'precio_noche'   => 'decimal:2',
        'subtotal'       => 'decimal:2',
        'descuento'      => 'decimal:2',
        'total'          => 'decimal:2',
    ];

    /* ---- Relaciones ---- */

    public function huesped(): BelongsTo
    {
        return $this->belongsTo(Huesped::class, 'huesped_id');
    }

    public function habitacion(): BelongsTo
    {
        return $this->belongsTo(Habitacion::class, 'habitacion_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function factura(): HasOne
    {
        return $this->hasOne(Factura::class, 'reserva_id');
    }

    public function cargosAdicionales(): HasMany
    {
        return $this->hasMany(CargoAdicional::class, 'reserva_id');
    }

    /* ---- Helpers ---- */

    public function getEstadoBadgeAttribute(): string
    {
        return match ($this->estado) {
            'pendiente'  => 'secondary',
            'confirmada' => 'primary',
            'checkin'    => 'success',
            'checkout'   => 'info',
            'cancelada'  => 'danger',
            'no_show'    => 'warning',
            default      => 'light',
        };
    }

    public static function generarCodigo(): string
    {
        $año = date('Y');
        $ultimo = static::whereYear('created_at', $año)->max('id') ?? 0;
        return "RES-{$año}-" . str_pad($ultimo + 1, 4, '0', STR_PAD_LEFT);
    }
}
