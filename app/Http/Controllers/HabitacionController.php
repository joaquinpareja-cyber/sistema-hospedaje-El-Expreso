<?php

namespace App\Http\Controllers;

use App\Models\Habitacion;
use App\Models\TipoHabitacion;
use Illuminate\Http\Request;

class HabitacionController extends Controller
{
    public function index(Request $request)
    {
        Habitacion::sincronizarEstadosDelDia();

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

        $habitaciones = $query->ordenNumerico()->paginate(15)->withQueryString();
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
            'estado'            => 'required|in:disponible,mantenimiento',
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

        $estado = $data['estado'];
        unset($data['estado']);
        $habitacion->update($data);

        if ($error = $this->aplicarEstado($habitacion, $estado)) {
            return redirect()->route('habitaciones.index')
                ->with('info', "Habitación {$habitacion->numero} actualizada, pero no se cambió el estado: {$error}");
        }

        return redirect()->route('habitaciones.index')
            ->with('success', "Habitación {$habitacion->numero} actualizada.");
    }

    public function destroy(Habitacion $habitacion)
    {
        if ($habitacion->reservas()->whereIn('estado', Habitacion::ESTADOS_RESERVA_ACTIVOS)->exists()) {
            return back()->with('error', 'No se puede eliminar una habitación con reservas activas.');
        }
        if ($habitacion->reservas()->exists()) {
            // Con historial no se puede borrar (FK restrict): se desactiva
            $habitacion->update(['activa' => false]);
            return redirect()->route('habitaciones.index')
                ->with('info', "La habitación {$habitacion->numero} tiene historial de reservas: se desactivó en lugar de eliminarse.");
        }
        $habitacion->delete();
        return redirect()->route('habitaciones.index')
            ->with('success', 'Habitación eliminada correctamente.');
    }

    public function cambiarEstado(Request $request, Habitacion $habitacion)
    {
        $request->validate(['estado' => 'required|in:disponible,mantenimiento']);

        if ($error = $this->aplicarEstado($habitacion, $request->estado)) {
            return back()->with('error', $error);
        }
        return back()->with('success', "Estado actualizado: {$habitacion->fresh()->estado}.");
    }

    /**
     * Manualmente solo se pone o se quita el mantenimiento. "Ocupada" y
     * "reservada" se derivan de las reservas (check-in / llegada del día).
     * Devuelve un mensaje de error o null.
     */
    private function aplicarEstado(Habitacion $habitacion, string $estado): ?string
    {
        if ($estado === $habitacion->estado) {
            return null;
        }

        if ($estado === 'mantenimiento') {
            if ($habitacion->reservas()->where('estado', 'checkin')->exists()) {
                return 'la habitación tiene un huésped alojado.';
            }
            $habitacion->update(['estado' => 'mantenimiento']);
            return null;
        }

        if ($estado === 'disponible') {
            $habitacion->update(['estado' => 'disponible']);
            $habitacion->recalcularEstado();   // si tiene huésped o llegada hoy, lo refleja
            return null;
        }

        return 'los estados "ocupada" y "reservada" se asignan automáticamente con las reservas.';
    }
}
