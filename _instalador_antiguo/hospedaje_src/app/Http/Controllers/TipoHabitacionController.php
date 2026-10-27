<?php

namespace App\Http\Controllers;

use App\Models\TipoHabitacion;
use Illuminate\Http\Request;

class TipoHabitacionController extends Controller
{
    public function index()
    {
        $tipos = TipoHabitacion::withCount('habitaciones')->orderBy('nombre')->get();
        return view('tipo_habitaciones.index', compact('tipos'));
    }

    public function create()
    {
        return view('tipo_habitaciones.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'      => 'required|string|max:80|unique:tipo_habitaciones,nombre',
            'descripcion' => 'nullable|string',
            'capacidad'   => 'required|integer|min:1|max:20',
            'precio_base' => 'required|numeric|min:0',
            'activo'      => 'boolean',
        ]);

        TipoHabitacion::create($data);

        return redirect()->route('tipo-habitaciones.index')
            ->with('success', "Tipo \"{$data['nombre']}\" creado correctamente.");
    }

    public function edit(TipoHabitacion $tipoHabitacion)
    {
        return view('tipo_habitaciones.edit', compact('tipoHabitacion'));
    }

    public function update(Request $request, TipoHabitacion $tipoHabitacion)
    {
        $data = $request->validate([
            'nombre'      => "required|string|max:80|unique:tipo_habitaciones,nombre,{$tipoHabitacion->id}",
            'descripcion' => 'nullable|string',
            'capacidad'   => 'required|integer|min:1|max:20',
            'precio_base' => 'required|numeric|min:0',
            'activo'      => 'boolean',
        ]);

        $tipoHabitacion->update($data);

        return redirect()->route('tipo-habitaciones.index')
            ->with('success', "Tipo \"{$tipoHabitacion->nombre}\" actualizado.");
    }

    public function destroy(TipoHabitacion $tipoHabitacion)
    {
        if ($tipoHabitacion->habitaciones()->exists()) {
            return back()->with('error', "No se puede eliminar: existen habitaciones de tipo \"{$tipoHabitacion->nombre}\".");
        }
        $tipoHabitacion->delete();
        return redirect()->route('tipo-habitaciones.index')
            ->with('success', 'Tipo de habitación eliminado.');
    }

    public function toggleActivo(TipoHabitacion $tipoHabitacion)
    {
        $tipoHabitacion->update(['activo' => !$tipoHabitacion->activo]);
        $estado = $tipoHabitacion->activo ? 'activado' : 'desactivado';
        return back()->with('success', "Tipo \"{$tipoHabitacion->nombre}\" $estado.");
    }
}
