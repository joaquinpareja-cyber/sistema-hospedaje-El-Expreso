<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Huesped extends Model
{
    use SoftDeletes;

    protected $table = 'huespedes';

    protected $fillable = [
        'nombre',
        'apellido',
        'tipo_documento',
        'num_documento',
        'nacionalidad',
        'fecha_nacimiento',
        'genero',
        'telefono',
        'email',
        'direccion',
        'observaciones',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
    ];

    /* ---- Relaciones ---- */

    public function reservas(): HasMany
    {
        return $this->hasMany(Reserva::class, 'huesped_id');
    }

    public function facturas(): HasMany
    {
        return $this->hasMany(Factura::class, 'huesped_id');
    }

    /* ---- Accessors ---- */

    public function getNombreCompletoAttribute(): string
    {
        return "{$this->nombre} {$this->apellido}";
    }

    public function getEdadAttribute(): ?int
    {
        return $this->fecha_nacimiento
            ? $this->fecha_nacimiento->age
            : null;
    }
}
