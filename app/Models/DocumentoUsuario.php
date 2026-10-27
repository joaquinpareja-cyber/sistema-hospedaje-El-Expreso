<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Documento personal de un empleado, guardado en disco privado (no accesible por URL pública) */
class DocumentoUsuario extends Model
{
    protected $table = 'documentos_usuario';

    /** Documentos que se piden (clave => datos para mostrar) */
    public const TIPOS = [
        'servicio_basico'  => ['label' => 'Factura de luz o agua',   'icono' => 'fa-file-invoice',    'ayuda' => 'Factura reciente de luz o agua de su domicilio.'],
        'titulo_bachiller' => ['label' => 'Título de bachiller',     'icono' => 'fa-graduation-cap',  'ayuda' => 'Escaneo o foto clara del título.'],
        'croquis'          => ['label' => 'Croquis del domicilio',   'icono' => 'fa-map-marked-alt',  'ayuda' => 'Dibujo o captura de mapa con la ubicación de su casa.'],
    ];

    /** Formatos aceptados y tamaño máximo (KB) */
    public const EXTENSIONES = ['pdf', 'jpg', 'jpeg', 'png'];
    public const MAX_KB = 5120;

    protected $fillable = ['user_id', 'tipo', 'archivo', 'nombre_original', 'mime', 'tamano'];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getEsPdfAttribute(): bool
    {
        return $this->mime === 'application/pdf';
    }

    public function getTamanoLegibleAttribute(): string
    {
        return $this->tamano >= 1048576
            ? number_format($this->tamano / 1048576, 1) . ' MB'
            : max(1, round($this->tamano / 1024)) . ' KB';
    }
}
