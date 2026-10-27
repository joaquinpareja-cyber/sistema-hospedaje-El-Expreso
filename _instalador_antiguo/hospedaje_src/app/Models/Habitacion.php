<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Habitacion extends Model
{
    protected $table = 'habitaciones';

    protected $fillable = [
        'numero',
        'piso',
        'tipo_habitacion_id',
        'estado',
        'descripcion',
        'imagen',
        'activa',
    ];

    protected $casts = [
        'activa' => 'boolean',
    ];

    /* ---- Relaciones ---- */

    public function tipoHabitacion(): BelongsTo
    {
        return $this->belongsTo(TipoHabitacion::class, 'tipo_habitacion_id');
    }

    public function reservas(): HasMany
    {
        return $this->hasMany(Reserva::class, 'habitacion_id');
    }

    /* ---- Scopes ---- */

    public function scopeDisponibles($query)
    {
        return $query->where('estado', 'disponible')->where('activa', true);
    }

    /* ---- Helpers ---- */

    public function getEstadoBadgeAttribute(): string
    {
        return match ($this->estado) {
            'disponible'   => 'success',
            'ocupada'      => 'danger',
            'reservada'    => 'warning',
            'mantenimiento'=> 'secondary',
            default        => 'light',
        };
    }

    public function estaDisponible(string $entrada, string $salida, ?int $excluirReservaId = null): bool
    {
        $query = $this->reservas()
            ->whereIn('estado', ['confirmada', 'checkin'])
            ->where(function ($q) use ($entrada, $salida) {
                $q->whereBetween('fecha_entrada', [$entrada, $salida])
                  ->orWhereBetween('fecha_salida', [$entrada, $salida])
                  ->orWhere(function ($q2) use ($entrada, $salida) {
                      $q2->where('fecha_entrada', '<=', $entrada)
                         ->where('fecha_salida', '>=', $salida);
                  });
            });

        if ($excluirReservaId) {
            $query->where('id', '!=', $excluirReservaId);
        }

        return $query->count() === 0;
    }
}
