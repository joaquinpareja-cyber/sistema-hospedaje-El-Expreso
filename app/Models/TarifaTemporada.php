<?php

namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TarifaTemporada extends Model
{
    protected $table = 'tarifas_temporada';

    protected $fillable = [
        'nombre', 'tipo_habitacion_id', 'fecha_inicio', 'fecha_fin',
        'precio_noche', 'tipo_precio', 'descripcion', 'activa', 'prioridad',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin'    => 'date',
        'precio_noche' => 'decimal:2',
        'activa'       => 'boolean',
    ];

    public function tipoHabitacion(): BelongsTo
    {
        return $this->belongsTo(TipoHabitacion::class, 'tipo_habitacion_id');
    }

    /** Obtiene el precio aplicable para un tipo de habitación en una fecha */
    public static function getPrecioAplicable(int $tipoHabitacionId, string $fecha, float $precioBase): float
    {
        return static::calcularEstancia($tipoHabitacionId, $fecha, Carbon::parse($fecha)->addDay()->toDateString(), $precioBase)['precio_noche'];
    }

    /**
     * Calcula el precio de cada noche de la estancia aplicando la tarifa de
     * temporada vigente esa noche (la de mayor prioridad; a igual prioridad,
     * la específica del tipo de habitación gana a la general).
     *
     * @return array{subtotal: float, precio_noche: float, num_noches: int}
     */
    public static function calcularEstancia(int $tipoHabitacionId, string $entrada, string $salida, float $precioBase): array
    {
        $desde = Carbon::parse($entrada)->startOfDay();
        $hasta = Carbon::parse($salida)->startOfDay();
        $noches = (int) max(0, $desde->diffInDays($hasta, false));

        if ($noches === 0) {
            return ['subtotal' => 0.0, 'precio_noche' => round($precioBase, 2), 'num_noches' => 0];
        }

        $tarifas = static::where('activa', true)
            ->where(function ($q) use ($tipoHabitacionId) {
                $q->whereNull('tipo_habitacion_id')
                  ->orWhere('tipo_habitacion_id', $tipoHabitacionId);
            })
            ->whereDate('fecha_inicio', '<', $hasta)
            ->whereDate('fecha_fin', '>=', $desde)
            ->get()
            ->sortBy([
                ['prioridad', 'desc'],
                fn($a, $b) => is_null($a->tipo_habitacion_id) <=> is_null($b->tipo_habitacion_id),
            ]);

        $subtotal = 0.0;
        foreach (CarbonPeriod::create($desde, $hasta->copy()->subDay()) as $noche) {
            $tarifa = $tarifas->first(fn($t) => $noche->between($t->fecha_inicio, $t->fecha_fin));
            $subtotal += $tarifa ? $tarifa->precioPara($precioBase) : $precioBase;
        }

        return [
            'subtotal'     => round($subtotal, 2),
            'precio_noche' => round($subtotal / $noches, 2),
            'num_noches'   => $noches,
        ];
    }

    /** Precio por noche que resulta de aplicar esta tarifa sobre el precio base */
    public function precioPara(float $precioBase): float
    {
        return $this->tipo_precio === 'porcentaje'
            ? round($precioBase * (1 + (float) $this->precio_noche / 100), 2)
            : (float) $this->precio_noche;
    }

    public function getEstadoAttribute(): string
    {
        $hoy = Carbon::today();
        if (!$this->activa)                   return 'inactiva';
        if ($this->fecha_fin->lt($hoy))       return 'vencida';
        if ($this->fecha_inicio->gt($hoy))    return 'proxima';
        return 'activa';
    }

    public function getEstadoBadgeAttribute(): string
    {
        return match($this->estado) {
            'activa'   => 'success',
            'proxima'  => 'info',
            'vencida'  => 'secondary',
            default    => 'light',
        };
    }
}
