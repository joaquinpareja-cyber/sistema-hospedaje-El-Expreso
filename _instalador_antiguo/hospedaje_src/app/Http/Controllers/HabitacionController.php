<?php

namespace App\Http\Controllers;

use App\Models\Habitacion;
use App\Models\TipoHabitacion;
use Illuminate\Http\Request;

class HabitacionController extends Controller
{
    public function index(Request $request)
    {
        $query = Habitacion::with('tipoHabitacion');

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }
        if ($request->filled('tipo')) {
            $query->where('tipo_habitacion_id', $request->tipo);
        }
        if ($request->filled('piso')) {
            $query->where('piso', $request->piso);
        }

        $habitaciones = $query->orderBy('numero')->paginate(15);
        $tipos        = TipoHabitacion::activos()->get();

        return view('habitaciones.index', compact('habitaciones', 'tipos'));
    }

    public function create()
    {
        $tipos = TipoHabitacion::activos()->get();
        return view('habitaciones.create', compact('tipos'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'numero'            => 'required|string|max:10|unique:habitaciones,numero',
            'piso'              => 'nullable|string|max:10',
            'tipo_habitacion_id'=> 'required|exists:tipo_habitaciones,id',
            'estado'            => 'required|in:disponible,ocupada,mantenimiento,reservada',
            'descripcion'       => 'nullable|string',
            'activa'            => 'boolean',
        ]);

        Habitacion::create($data);

        return redirect()->route('habitaciones.index')
            ->with('success', "Habitación {$data['numero']} creada correctamente.");
    }

    public function show(Habitacion $habitacion)
    {
        $habitacion->load(['tipoHabitacion', 'reservas.huesped']);
        $reservasActivas = $habitacion->reservas()
            ->whereIn('estado', ['confirmada', 'checkin'])
            ->with('huesped')
            ->orderBy('fecha_entrada')
            ->get();

        return view('habitaciones.show', compact('habitacion', 'reservasActivas'));
    }

    public function edit(Habitacion $habitacion)
    {
        $tipos = TipoHabitacion::activos()->get();
        return view('habitaciones.edit', compact('habitacion', 'tipos'));
    }

    public function update(Request $request, Habitacion $habitacion)
    {
        $data = $request->validate([
            'numero'            => "required|string|max:10|unique:habitaciones,numero,{$habitacion->id}",
            'piso'              => 'nullable|string|max:10',
            'tipo_habitacion_id'=> 'required|exists:tipo_habitaciones,id',
            'estado'            => 'required|in:disponible,ocupada,mantenimiento,reservada',
            'descripcion'       => 'nullable|string',
            'activa'            => 'boolean',
        ]);

        $habitacion->update($data);

        return redirect()->route('habitaciones.index')
            ->with('success', "Habitación {$habitacion->numero} actualizada.");
    }

    public function destroy(Habitacion $habitacion)
    {
        if ($habitacion->reservas()->whereIn('estado', ['checkin', 'confirmada'])->exists()) {
            return back()->with('error', 'No se puede eliminar una habitación con reservas activas.');
        }
        $habitacion->delete();
        return redirect()->route('habitaciones.index')
            ->with('success', 'Habitación eliminada correctamente.');
    }

    public function cambiarEstado(Request $request, Habitacion $habitacion)
    {
        $request->validate(['estado' => 'required|in:disponible,ocupada,mantenimiento,reservada']);
        $habitacion->update(['estado' => $request->estado]);
        return back()->with('success', "Estado cambiado a {$request->estado}.");
    }
}
