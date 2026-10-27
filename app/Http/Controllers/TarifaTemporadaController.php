<?php

namespace App\Http\Controllers;

use App\Models\TarifaTemporada;
use App\Models\TipoHabitacion;
use Illuminate\Http\Request;

class TarifaTemporadaController extends Controller
{
    public function index()
    {
        $tarifas = TarifaTemporada::with('tipoHabitacion')
            ->orderByDesc('activa')
            ->orderBy('fecha_inicio')
            ->get();

        return view('tarifas.index', compact('tarifas'));
    }

    public function create()
    {
        $tipos = TipoHabitacion::where('activo', true)->orderBy('nombre')->get();
        return view('tarifas.create', compact('tipos'));
    }

    public function store(Request $request)
    {
        $data = $this->validar($request);

        TarifaTemporada::create($data);

        return redirect()->route('tarifas.index')
            ->with('success', "Tarifa «{$data['nombre']}» creada correctamente.");
    }

    public function edit(TarifaTemporada $tarifa)
    {
        $tipos = TipoHabitacion::where('activo', true)->orderBy('nombre')->get();
        return view('tarifas.edit', compact('tarifa', 'tipos'));
    }

    public function update(Request $request, TarifaTemporada $tarifa)
    {
        $tarifa->update($this->validar($request));

        return redirect()->route('tarifas.index')
            ->with('success', "Tarifa «{$tarifa->nombre}» actualizada correctamente.");
    }

    public function destroy(TarifaTemporada $tarifa)
    {
        $nombre = $tarifa->nombre;
        $tarifa->delete();
        return back()->with('success', "Tarifa «{$nombre}» eliminada.");
    }

    public function toggle(TarifaTemporada $tarifa)
    {
        $tarifa->update(['activa' => !$tarifa->activa]);
        $estado = $tarifa->activa ? 'activada' : 'desactivada';
        return back()->with('success', "Tarifa «{$tarifa->nombre}» {$estado}.");
    }

    private function validar(Request $request): array
    {
        // En porcentaje se admiten valores negativos (descuento de temporada baja)
        $minPrecio = $request->input('tipo_precio') === 'porcentaje' ? -100 : 0;

        $data = $request->validate([
            'nombre'              => 'required|string|max:100',
            'tipo_habitacion_id'  => 'nullable|exists:tipo_habitaciones,id',
            'fecha_inicio'        => 'required|date',
            'fecha_fin'           => 'required|date|after_or_equal:fecha_inicio',
            'precio_noche'        => "required|numeric|min:{$minPrecio}|max:99999",
            'tipo_precio'         => 'required|in:fijo,porcentaje',
            'descripcion'         => 'nullable|string|max:300',
            'prioridad'           => 'nullable|integer|min:0|max:10',
            'activa'              => 'boolean',
        ], [
            'nombre.required'          => 'El nombre de la tarifa es obligatorio.',
            'fecha_fin.after_or_equal' => 'La fecha fin debe ser igual o posterior a la fecha inicio.',
            'precio_noche.min'         => $minPrecio < 0
                ? 'El porcentaje no puede ser menor a -100%.'
                : 'El precio debe ser mayor o igual a 0.',
        ]);

        $data['activa']    = $request->boolean('activa');
        $data['prioridad'] = $data['prioridad'] ?? 0;

        return $data;
    }
}
