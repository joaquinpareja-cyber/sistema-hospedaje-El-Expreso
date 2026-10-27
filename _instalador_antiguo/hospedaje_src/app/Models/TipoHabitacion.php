<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoHabitacion extends Model
{
    protected $table = 'tipo_habitaciones';

    protected $fillable = [
        'nombre',
        'descripcion',
        'capacidad',
        'precio_base',
        'activo',
    ];

    protected $casts = [
        'precio_base' => 'decimal:2',
        'activo'      => 'boolean',
    ];

    /* ---- Relaciones ---- */

    public function habitaciones(): HasMany
    {
        return $this->hasMany(Habitacion::class, 'tipo_habitacion_id');
    }

    /* ---- Scopes ---- */

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}
