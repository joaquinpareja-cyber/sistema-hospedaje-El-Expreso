<?php

namespace App\Providers;

use App\Models\Configuracion;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Datos del hotel (Configuración) disponibles en el layout y los comprobantes
        View::composer(['layouts.app', 'facturas.pdf'], function ($view) {
            static $empresa = null;
            $empresa ??= $this->datosEmpresa();
            $view->with('empresa', $empresa);
        });

        // Símbolo de moneda (Configuración → Facturación) disponible en todas las vistas
        View::composer('*', function ($view) {
            static $moneda = null;
            $moneda ??= self::monedaSimbolo();
            $view->with('moneda', $moneda);
        });
    }

    public static function monedaSimbolo(): string
    {
        try {
            return Configuracion::get('facturacion_moneda_simbolo') ?: 'Bs.';
        } catch (\Throwable) {
            return 'Bs.';   // sin conexión a BD (p. ej. durante la instalación)
        }
    }

    private function datosEmpresa(): array
    {
        $defaults = [
            'nombre'        => config('app.name'),
            'razon_social'  => null,
            'nit'           => null,
            'direccion'     => null,
            'telefono'      => null,
            'email'         => null,
            'eslogan'       => null,
            'logo'          => null,
            'pie'           => 'Gracias por su preferencia.',
            'color_sidebar' => '#1a2035',
            'color_brand'   => '#141d2e',
        ];

        try {
            if (!Schema::hasTable('configuraciones')) {
                return $defaults;
            }
        } catch (\Throwable) {
            return $defaults;   // sin conexión a BD (p. ej. durante la instalación)
        }

        $color = fn($v, $d) => is_string($v) && preg_match('/^#[0-9a-fA-F]{3,8}$/', $v) ? $v : $d;

        return [
            'nombre'        => Configuracion::get('empresa_nombre') ?: $defaults['nombre'],
            'razon_social'  => Configuracion::get('empresa_razon_social'),
            'nit'           => Configuracion::get('empresa_nit'),
            'direccion'     => Configuracion::get('empresa_direccion'),
            'telefono'      => Configuracion::get('empresa_telefono'),
            'email'         => Configuracion::get('empresa_email'),
            'eslogan'       => Configuracion::get('empresa_eslogan'),
            'logo'          => Configuracion::get('empresa_logo'),
            'pie'           => Configuracion::get('facturacion_pie_factura') ?: $defaults['pie'],
            'color_sidebar' => $color(Configuracion::get('sistema_color_sidebar'), $defaults['color_sidebar']),
            'color_brand'   => $color(Configuracion::get('sistema_color_brand'), $defaults['color_brand']),
        ];
    }
}
