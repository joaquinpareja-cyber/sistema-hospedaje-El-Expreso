<?php

namespace App\Http\Controllers;

use App\Models\Huesped;
use App\Models\Reserva;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class HuespedController extends Controller
{
    public function index(Request $request)
    {
        $query = Huesped::query();

        if ($request->filled('buscar')) {
            $b = $request->buscar;
            $query->where(function ($q) use ($b) {
                $q->where('nombre_completo', 'like', "%{$b}%")
                  ->orWhere('num_documento', 'like', "%{$b}%");
            });
        }

        if ($request->filled('tipo_documento')) {
            $query->where('tipo_documento', $request->tipo_documento);
        }

        $huespedes = $query->orderBy('nombre_completo')->paginate(15)->withQueryString();

        return view('huespedes.index', compact('huespedes'));
    }

    public function create(Request $request)
    {
        // Si viene de una reserva, el huésped nuevo se agrega como acompañante
        $reserva = $request->filled('reserva_id')
            ? Reserva::with('habitacion.tipoHabitacion')->find($request->reserva_id)
            : null;

        return view('huespedes.create', compact('reserva'));
    }

    public function store(Request $request)
    {
        $data = $request->validate(Huesped::reglas());

        if ($request->filled('reserva_id')) {
            $reserva = Reserva::with('habitacion.tipoHabitacion')->findOrFail($request->reserva_id);

            $huesped = DB::transaction(function () use ($data, $reserva) {
                $huesped = Huesped::create($data);
                if ($error = $reserva->agregarAcompanante($huesped)) {
                    throw ValidationException::withMessages(['reserva_id' => $error]);
                }
                return $huesped;
            });

            return redirect()->route('reservas.show', $reserva)
                ->with('success', "{$huesped->nombre_completo} registrado y agregado como acompañante.");
        }

        $huesped = Huesped::create($data);

        return redirect()->route('huespedes.show', $huesped)
            ->with('success', "Huésped {$huesped->nombre_completo} registrado correctamente.");
    }

    public function show(Huesped $huesped)
    {
        $huesped->load(['reservas.habitacion.tipoHabitacion', 'reservasComoAcompanante.habitacion.tipoHabitacion', 'reservasComoAcompanante.huesped', 'facturas']);
        $totalEstancias = $huesped->reservas()->whereIn('estado', ['checkout'])->count();
        $totalGastado   = $huesped->facturas()->where('estado', 'pagada')->sum('total');

        return view('huespedes.show', compact('huesped', 'totalEstancias', 'totalGastado'));
    }

    public function edit(Huesped $huesped)
    {
        return view('huespedes.edit', compact('huesped'));
    }

    public function update(Request $request, Huesped $huesped)
    {
        $data = $request->validate(Huesped::reglas($huesped->id));

        $huesped->update($data);

        return redirect()->route('huespedes.show', $huesped)
            ->with('success', 'Datos del huésped actualizados.');
    }

    public function destroy(Huesped $huesped)
    {
        if ($huesped->reservas()->whereIn('estado', ['pendiente', 'confirmada', 'checkin'])->exists()
            || $huesped->reservasComoAcompanante()->whereIn('estado', ['pendiente', 'confirmada', 'checkin'])->exists()) {
            return back()->with('error', 'No se puede eliminar un huésped con reservas activas.');
        }
        $huesped->delete();
        return redirect()->route('huespedes.index')
            ->with('success', 'Huésped eliminado del sistema.');
    }

    /** Búsqueda AJAX para el formulario de reservas */
    public function buscarAjax(Request $request)
    {
        $q = trim((string) $request->q);
        if (mb_strlen($q) < 2) {
            return response()->json(['results' => []]);
        }

        $huespedes = Huesped::where(function ($w) use ($q) {
                $w->where('num_documento', 'like', "%{$q}%")
                  ->orWhere('nombre_completo', 'like', "%{$q}%");
            })
            ->orderBy('nombre_completo')
            ->select('id', 'nombre_completo', 'num_documento', 'tipo_documento')
            ->limit(10)
            ->get()
            ->map(fn($h) => [
                'id'    => $h->id,
                'text'  => "{$h->nombre_completo} ({$h->tipo_documento}: {$h->num_documento})",
            ]);

        return response()->json(['results' => $huespedes]);
    }
}
