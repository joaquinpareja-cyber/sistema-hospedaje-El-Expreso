<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ConfiguracionController extends Controller
{
    public function index()
    {
        $config = Configuracion::all()->keyBy('clave');
        return view('configuracion.index', compact('config'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'empresa_nombre'              => 'required|string|max:150',
            'empresa_nit'                 => 'nullable|digits_between:5,15',
            'empresa_email'               => 'nullable|email|max:150',
            'empresa_web'                 => 'nullable|url|max:200',
            'facturacion_iva'             => 'required|numeric|min:0|max:100',
            'facturacion_moneda_simbolo'  => 'required|string|max:5',
            'empresa_logo'                => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',  // sin SVG: puede contener scripts
            'sistema_color_sidebar'       => ['nullable', 'regex:/^#[0-9a-fA-F]{3,8}$/'],
            'sistema_color_brand'         => ['nullable', 'regex:/^#[0-9a-fA-F]{3,8}$/'],
            'facturacion_serie_boleta'    => ['nullable', 'regex:/^[A-Za-z0-9]{1,4}$/'],
            'facturacion_serie_factura'   => ['nullable', 'regex:/^[A-Za-z0-9]{1,4}$/'],
            'facturacion_serie_recibo'    => ['nullable', 'regex:/^[A-Za-z0-9]{1,4}$/'],
        ], [
            'empresa_nombre.required'     => 'El nombre del hotel es obligatorio.',
            'empresa_nit.digits_between'  => 'El NIT debe tener entre 5 y 15 dígitos.',
            'empresa_email.email'         => 'El correo electrónico no es válido.',
            'empresa_logo.image'          => 'El logo debe ser una imagen.',
            'empresa_logo.max'            => 'El logo no debe superar 2MB.',
            'empresa_logo.mimes'          => 'El logo debe ser PNG, JPG o WEBP.',
            'sistema_color_sidebar.regex' => 'El color debe tener formato hexadecimal (#1a2035).',
            'sistema_color_brand.regex'   => 'El color debe tener formato hexadecimal (#141d2e).',
            'facturacion_serie_boleta.regex'  => 'La serie debe tener hasta 4 letras o números (ej: B001).',
            'facturacion_serie_factura.regex' => 'La serie debe tener hasta 4 letras o números (ej: F001).',
            'facturacion_serie_recibo.regex'  => 'La serie debe tener hasta 4 letras o números (ej: R001).',
        ]);

        // ── Guardar logo ──────────────────────────────────────────────
        if ($request->hasFile('empresa_logo')) {
            // Eliminar logo anterior
            $logoAnterior = Configuracion::get('empresa_logo');
            if ($logoAnterior && Storage::disk('public')->exists(basename($logoAnterior))) {
                Storage::disk('public')->delete(basename($logoAnterior));
            }
            $ext = $request->file('empresa_logo')->extension(); // según el contenido real, no el nombre enviado
            $request->file('empresa_logo')->storeAs('/', 'logo.' . $ext, 'public');
            Configuracion::set('empresa_logo', 'storage/logo.' . $ext);
        }

        // ── Guardar todos los demás campos ────────────────────────────
        $campos = [
            'empresa_nombre', 'empresa_razon_social', 'empresa_nit',
            'empresa_direccion', 'empresa_telefono', 'empresa_email',
            'empresa_web', 'empresa_eslogan',
            'facturacion_moneda_simbolo', 'facturacion_moneda_nombre',
            'facturacion_iva', 'facturacion_serie_boleta',
            'facturacion_serie_factura', 'facturacion_serie_recibo',
            'facturacion_pie_factura',
            'sistema_zona_horaria', 'sistema_formato_fecha',
            'sistema_color_sidebar', 'sistema_color_brand',
        ];

        foreach ($campos as $campo) {
            if ($request->has($campo)) {
                Configuracion::set($campo, $request->input($campo));
            }
        }

        Configuracion::limpiarCache();

        return back()->with('success', 'Configuración guardada correctamente.');
    }

    public function eliminarLogo()
    {
        $logo = Configuracion::get('empresa_logo');
        if ($logo) {
            foreach (['logo.png', 'logo.jpg', 'logo.jpeg', 'logo.webp', 'logo.svg'] as $f) {
                Storage::disk('public')->delete($f);
            }
            Configuracion::set('empresa_logo', null);
        }
        return back()->with('success', 'Logo eliminado correctamente.');
    }
}
