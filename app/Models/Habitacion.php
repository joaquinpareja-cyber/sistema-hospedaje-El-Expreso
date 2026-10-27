<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Habitacion extends Model
{
    protected $table = 'habitaciones';

    /** Estados de reserva que bloquean la habitación */
    public const ESTADOS_RESERVA_ACTIVOS = ['pendiente', 'confirmada', 'checkin'];

    protected $fillable = [
        'numero',
        'piso',
        'tipo_habitacion_id',
        'estado',
        'estado_limpieza',
        'limpieza_actualizado',
        'limpieza_notas',
        'limpieza_user_id',
        'descripcion',
        'imagen',
        'activa',
    ];

    protected $casts = [
        'activa'               => 'boolean',
        'limpieza_actualizado' => 'datetime',
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

    // "numero" es texto: ordena 1, 2, ... 10 (no 1, 10, 11, 2) y desempata alfabéticamente (101A, 101B)
    public function scopeOrdenNumerico($query)
    {
        return $query->orderByRaw('CAST(numero AS UNSIGNED)')->orderBy('numero');
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

    /**
     * Dos estancias se cruzan si una empieza antes de que termine la otra.
     * El día de salida queda libre: una reserva puede entrar el mismo día
     * en que sale la anterior.
     */
    public function estaDisponible(string $entrada, string $salida, ?int $excluirReservaId = null): bool
    {
        $query = $this->reservas()
            ->whereIn('estado', self::ESTADOS_RESERVA_ACTIVOS)
            ->whereDate('fecha_entrada', '<', $salida)
            ->whereDate('fecha_salida', '>', $entrada);

        if ($excluirReservaId) {
            $query->where('id', '!=', $excluirReservaId);
        }

        return !$query->exists();
    }

    /**
     * Recalcula una vez al día el estado de todas las habitaciones, para que
     * las llegadas del día aparezcan como "reservadas" sin intervención manual.
     * También se ejecuta desde el scheduler (app/Console/Kernel.php).
     */
    public static function sincronizarEstadosDelDia(bool $forzar = false): void
    {
        $clave = 'habitaciones_sync_' . Carbon::today()->toDateString();
        if (!$forzar && !Cache::add($clave, true, Carbon::tomorrow())) {
            return;
        }

        static::where('activa', true)->where('estado', '!=', 'mantenimiento')
            ->get()
            ->each->recalcularEstado();
    }

    /**
     * Recalcula el estado operativo de la habitación a partir de sus reservas,
     * para no dejarla "disponible" cuando aún tiene un huésped o una llegada hoy.
     * Una habitación en mantenimiento se respeta: solo se cambia manualmente.
     */
    public function recalcularEstado(): void
    {
        if ($this->estado === 'mantenimiento') {
            return;
        }

        $hoy = Carbon::today();

        if ($this->reservas()->where('estado', 'checkin')->exists()) {
            $estado = 'ocupada';
        } elseif ($this->reservas()
            ->whereIn('estado', ['pendiente', 'confirmada'])
            ->whereDate('fecha_entrada', '<=', $hoy)
            ->whereDate('fecha_salida', '>', $hoy)
            ->exists()) {
            $estado = 'reservada';
        } else {
            $estado = 'disponible';
        }

        if ($estado !== $this->estado) {
            $this->update(['estado' => $estado]);
        }
    }
}
