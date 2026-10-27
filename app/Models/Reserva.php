<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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

    /** Personas que se alojan con el titular (no incluye al titular) */
    public function acompanantes(): BelongsToMany
    {
        return $this->belongsToMany(Huesped::class, 'reserva_acompanantes', 'reserva_id', 'huesped_id')
            ->withTimestamps()
            ->orderBy('reserva_acompanantes.id');
    }

    public function habitacion(): BelongsTo
    {
        return $this->belongsTo(Habitacion::class, 'habitacion_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** Comprobante vigente (no anulado) de la reserva */
    public function factura(): HasOne
    {
        return $this->hasOne(Factura::class, 'reserva_id')->where('estado', '!=', 'anulada');
    }

    /** Todos los comprobantes, incluidos los anulados */
    public function facturas(): HasMany
    {
        return $this->hasMany(Factura::class, 'reserva_id');
    }

    public function cargosAdicionales(): HasMany
    {
        return $this->hasMany(CargoAdicional::class, 'reserva_id');
    }

    /* ---- Helpers ---- */

    /** Reservas a las que todavía se les puede agregar o quitar acompañantes */
    public function admiteCambiosDeAcompanantes(): bool
    {
        return in_array($this->estado, ['pendiente', 'confirmada', 'checkin']);
    }

    /**
     * Agrega un acompañante validando que no sea el titular, que no esté repetido
     * y que no se supere la capacidad de la habitación. Ajusta num_personas.
     * Devuelve un mensaje de error o null si todo salió bien.
     */
    public function agregarAcompanante(Huesped $huesped): ?string
    {
        if (!$this->admiteCambiosDeAcompanantes()) {
            return 'Esta reserva ya no admite cambios de acompañantes.';
        }
        if ($huesped->id === $this->huesped_id) {
            return "{$huesped->nombre_completo} ya es el titular de la reserva.";
        }
        if ($this->acompanantes()->whereKey($huesped->id)->exists()) {
            return "{$huesped->nombre_completo} ya figura como acompañante.";
        }

        $capacidad = $this->habitacion->tipoHabitacion->capacidad;
        $personas  = 1 + $this->acompanantes()->count() + 1;
        if ($personas > $capacidad) {
            return "La habitación {$this->habitacion->numero} admite como máximo {$capacidad} persona(s).";
        }

        $this->acompanantes()->attach($huesped->id);
        if ($this->num_personas < $personas) {
            $this->update(['num_personas' => $personas]);
        }

        return null;
    }

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

    /**
     * Siguiente código correlativo del año (RES-2026-0001).
     * Debe llamarse dentro de una transacción: el bloqueo evita que dos
     * usuarios obtengan el mismo número al mismo tiempo.
     */
    public static function generarCodigo(): string
    {
        $prefijo = 'RES-' . date('Y') . '-';

        $ultimo = static::withTrashed()
            ->where('codigo', 'like', $prefijo . '%')
            ->lockForUpdate()
            ->pluck('codigo')
            ->map(fn($c) => (int) substr($c, strlen($prefijo)))
            ->max() ?? 0;

        return $prefijo . str_pad($ultimo + 1, 4, '0', STR_PAD_LEFT);
    }
}
