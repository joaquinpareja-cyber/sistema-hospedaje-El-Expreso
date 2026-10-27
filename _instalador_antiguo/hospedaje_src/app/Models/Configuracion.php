<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Configuracion extends Model
{
    protected $table = 'configuraciones';

    protected $fillable = ['clave', 'valor', 'grupo', 'tipo', 'descripcion'];

    /** Caché en memoria para la petición actual */
    protected static array $cache = [];

    /** Obtiene un valor de configuración */
    public static function get(string $clave, mixed $default = null): mixed
    {
        if (!array_key_exists($clave, static::$cache)) {
            static::$cache[$clave] = static::where('clave', $clave)->value('valor');
        }
        return static::$cache[$clave] ?? $default;
    }

    /** Guarda o actualiza un valor */
    public static function set(string $clave, mixed $valor): void
    {
        static::updateOrCreate(['clave' => $clave], ['valor' => $valor]);
        static::$cache[$clave] = $valor;
    }

    /** Devuelve todas las configuraciones de un grupo indexadas por clave */
    public static function grupo(string $grupo): \Illuminate\Support\Collection
    {
        return static::where('grupo', $grupo)->get()->keyBy('clave');
    }

    /** Limpia la caché */
    public static function limpiarCache(): void
    {
        static::$cache = [];
    }
}
