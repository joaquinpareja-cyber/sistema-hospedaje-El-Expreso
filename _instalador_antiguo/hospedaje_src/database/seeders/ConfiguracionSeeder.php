<?php

namespace Database\Seeders;

use App\Models\Configuracion;
use Illuminate\Database\Seeder;

class ConfiguracionSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            // ── Empresa ──────────────────────────────────────────────
            ['clave' => 'empresa_nombre',       'valor' => 'Mi Hotel',        'grupo' => 'empresa',     'tipo' => 'text',     'descripcion' => 'Nombre comercial del hotel'],
            ['clave' => 'empresa_razon_social',  'valor' => '',                'grupo' => 'empresa',     'tipo' => 'text',     'descripcion' => 'Razón social legal'],
            ['clave' => 'empresa_ruc',           'valor' => '',                'grupo' => 'empresa',     'tipo' => 'text',     'descripcion' => 'RUC de la empresa (11 dígitos)'],
            ['clave' => 'empresa_direccion',     'valor' => '',                'grupo' => 'empresa',     'tipo' => 'textarea', 'descripcion' => 'Dirección fiscal del hotel'],
            ['clave' => 'empresa_telefono',      'valor' => '',                'grupo' => 'empresa',     'tipo' => 'text',     'descripcion' => 'Teléfono principal'],
            ['clave' => 'empresa_email',         'valor' => '',                'grupo' => 'empresa',     'tipo' => 'text',     'descripcion' => 'Correo electrónico de contacto'],
            ['clave' => 'empresa_web',           'valor' => '',                'grupo' => 'empresa',     'tipo' => 'text',     'descripcion' => 'Sitio web oficial'],
            ['clave' => 'empresa_logo',          'valor' => null,              'grupo' => 'empresa',     'tipo' => 'image',    'descripcion' => 'Logotipo del hotel'],
            ['clave' => 'empresa_eslogan',       'valor' => '',                'grupo' => 'empresa',     'tipo' => 'text',     'descripcion' => 'Eslogan o lema del hotel'],
            // ── Facturación ──────────────────────────────────────────
            ['clave' => 'facturacion_moneda_simbolo',  'valor' => 'S/',    'grupo' => 'facturacion', 'tipo' => 'text',   'descripcion' => 'Símbolo de la moneda (S/, $, €...)'],
            ['clave' => 'facturacion_moneda_nombre',   'valor' => 'Soles', 'grupo' => 'facturacion', 'tipo' => 'text',   'descripcion' => 'Nombre de la moneda'],
            ['clave' => 'facturacion_igv',             'valor' => '18',    'grupo' => 'facturacion', 'tipo' => 'number', 'descripcion' => 'Porcentaje de IGV / impuesto (%)'],
            ['clave' => 'facturacion_serie_boleta',    'valor' => 'B001',  'grupo' => 'facturacion', 'tipo' => 'text',   'descripcion' => 'Serie para boletas de venta'],
            ['clave' => 'facturacion_serie_factura',   'valor' => 'F001',  'grupo' => 'facturacion', 'tipo' => 'text',   'descripcion' => 'Serie para facturas'],
            ['clave' => 'facturacion_serie_recibo',    'valor' => 'R001',  'grupo' => 'facturacion', 'tipo' => 'text',   'descripcion' => 'Serie para recibos de honorarios'],
            ['clave' => 'facturacion_pie_factura',     'valor' => 'Gracias por su preferencia.',  'grupo' => 'facturacion', 'tipo' => 'textarea', 'descripcion' => 'Texto al pie de cada comprobante'],
            // ── Sistema ──────────────────────────────────────────────
            ['clave' => 'sistema_zona_horaria',    'valor' => 'America/Lima', 'grupo' => 'sistema', 'tipo' => 'select',  'descripcion' => 'Zona horaria del servidor'],
            ['clave' => 'sistema_formato_fecha',   'valor' => 'd/m/Y',        'grupo' => 'sistema', 'tipo' => 'select',  'descripcion' => 'Formato de visualización de fechas'],
            ['clave' => 'sistema_color_sidebar',   'valor' => '#1a2035',      'grupo' => 'sistema', 'tipo' => 'color',   'descripcion' => 'Color del sidebar de navegación'],
            ['clave' => 'sistema_color_brand',     'valor' => '#141d2e',      'grupo' => 'sistema', 'tipo' => 'color',   'descripcion' => 'Color de la barra de marca'],
        ];

        foreach ($defaults as $cfg) {
            Configuracion::firstOrCreate(['clave' => $cfg['clave']], $cfg);
        }
    }
}
