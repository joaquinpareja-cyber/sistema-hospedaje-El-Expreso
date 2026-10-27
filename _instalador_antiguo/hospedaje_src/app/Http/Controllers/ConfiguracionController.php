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
            'empresa_ruc'                 => 'nullable|digits:11',
            'empresa_email'               => 'nullable|email|max:150',
            'empresa_web'                 => 'nullable|url|max:200',
            'facturacion_igv'             => 'required|numeric|min:0|max:100',
            'facturacion_moneda_simbolo'  => 'required|string|max:5',
            'empresa_logo'                => 'nullable|image|mimes:png,jpg,jpeg,svg|max:2048',
            'sistema_color_sidebar'       => 'nullable|string|max:10',
            'sistema_color_brand'         => 'nullable|string|max:10',
        ], [
            'empresa_nombre.required'     => 'El nombre del hotel es obligatorio.',
            'empresa_ruc.digits'          => 'El RUC debe tener exactamente 11 dígitos.',
            'empresa_email.email'         => 'El correo electrónico no es válido.',
            'empresa_logo.image'          => 'El logo debe ser una imagen.',
            'empresa_logo.max'            => 'El logo no debe superar 2MB.',
        ]);

        // ── Guardar logo ──────────────────────────────────────────────
        if ($request->hasFile('empresa_logo')) {
            // Eliminar logo anterior
            $logoAnterior = Configuracion::get('empresa_logo');
            if ($logoAnterior && Storage::disk('public')->exists(basename($logoAnterior))) {
                Storage::disk('public')->delete(basename($logoAnterior));
            }
            $ext  = $request->file('empresa_logo')->getClientOriginalExtension();
            $path = $request->file('empresa_logo')->storeAs('/', 'logo.' . $ext, 'public');
            Configuracion::set('empresa_logo', 'storage/logo.' . $ext);
        }

        // ── Guardar todos los demás campos ────────────────────────────
        $campos = [
            'empresa_nombre', 'empresa_razon_social', 'empresa_ruc',
            'empresa_direccion', 'empresa_telefono', 'empresa_email',
            'empresa_web', 'empresa_eslogan',
            'facturacion_moneda_simbolo', 'facturacion_moneda_nombre',
            'facturacion_igv', 'facturacion_serie_boleta',
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
            foreach (['logo.png', 'logo.jpg', 'logo.jpeg', 'logo.svg'] as $f) {
                Storage::disk('public')->delete($f);
            }
            Configuracion::set('empresa_logo', null);
        }
        return back()->with('success', 'Logo eliminado correctamente.');
    }
}
