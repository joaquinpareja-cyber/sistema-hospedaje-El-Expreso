<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Huesped extends Model
{
    use SoftDeletes;

    protected $table = 'huespedes';

    /** Tipos de documento de identidad (clave => etiqueta) */
    public const TIPOS_DOCUMENTO = [
        'CI'        => 'Cédula de Identidad (CI)',
        'Pasaporte' => 'Pasaporte',
        'CE'        => 'Cédula de Extranjero',
        'NIT'       => 'NIT',
    ];

    /** Estados civiles (clave => etiqueta) */
    public const ESTADOS_CIVILES = [
        'soltero'     => 'Soltero(a)',
        'casado'      => 'Casado(a)',
        'divorciado'  => 'Divorciado(a)',
        'viudo'       => 'Viudo(a)',
        'union_libre' => 'Unión libre',
    ];

    protected $fillable = [
        'nombre_completo',
        'tipo_documento',
        'num_documento',
        'nacionalidad',
        'procedencia',
        'profesion',
        'estado_civil',
        'fecha_nacimiento',
        'genero',
        'telefono',
        'observaciones',
    ];

    /** Reglas de validación de la ficha del huésped ($exceptoId: al editar, ignora su propio documento) */
    public static function reglas(?int $exceptoId = null): array
    {
        return [
            'nombre_completo' => 'required|string|max:200',
            'tipo_documento'  => 'required|in:' . implode(',', array_keys(self::TIPOS_DOCUMENTO)),
            'num_documento'   => 'required|string|max:20|unique:huespedes,num_documento' . ($exceptoId ? ",{$exceptoId}" : ''),
            'nacionalidad'    => 'nullable|string|max:60',
            'procedencia'     => 'nullable|string|max:100',
            'profesion'       => 'nullable|string|max:100',
            'estado_civil'    => 'nullable|in:' . implode(',', array_keys(self::ESTADOS_CIVILES)),
            'fecha_nacimiento'=> 'nullable|date|before:today',
            'genero'          => 'nullable|in:M,F,Otro',
            'telefono'        => 'nullable|string|max:20',
            'observaciones'   => 'nullable|string',
        ];
    }

    protected $casts = [
        'fecha_nacimiento' => 'date',
    ];

    /* ---- Relaciones ---- */

    public function reservas(): HasMany
    {
        return $this->hasMany(Reserva::class, 'huesped_id');
    }

    /** Reservas en las que se alojó como acompañante de otro titular */
    public function reservasComoAcompanante(): BelongsToMany
    {
        return $this->belongsToMany(Reserva::class, 'reserva_acompanantes', 'huesped_id', 'reserva_id')
            ->withTimestamps();
    }

    public function facturas(): HasMany
    {
        return $this->hasMany(Factura::class, 'huesped_id');
    }

    /* ---- Accessors ---- */

    public function getEdadAttribute(): ?int
    {
        return $this->fecha_nacimiento
            ? $this->fecha_nacimiento->age
            : null;
    }

    public function getEstadoCivilLabelAttribute(): ?string
    {
        return self::ESTADOS_CIVILES[$this->estado_civil] ?? null;
    }
}
