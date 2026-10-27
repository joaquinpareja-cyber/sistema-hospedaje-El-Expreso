<?php

namespace App\Http\Controllers;

use App\Models\Habitacion;
use App\Models\Huesped;
use App\Models\Reserva;
use App\Models\CargoAdicional;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReservaController extends Controller
{
    public function index(Request $request)
    {
        $query = Reserva::with(['huesped', 'habitacion.tipoHabitacion']);

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }
        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_entrada', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_salida', '<=', $request->fecha_hasta);
        }
        if ($request->filled('buscar')) {
            $b = $request->buscar;
            $query->where(function ($q) use ($b) {
                $q->where('codigo', 'like', "%{$b}%")
                  ->orWhereHas('huesped', fn($qh) => $qh->where('apellido', 'like', "%{$b}%")
                      ->orWhere('num_documento', 'like', "%{$b}%"));
            });
        }

        $reservas = $query->latest()->paginate(15);

        return view('reservas.index', compact('reservas'));
    }

    public function create(Request $request)
    {
        $habitaciones = Habitacion::with('tipoHabitacion')
            ->where('activa', true)
            ->orderBy('numero')
            ->get();
        $huespedes = Huesped::orderBy('apellido')->get();
        $huespedSeleccionado = $request->huesped_id
            ? Huesped::find($request->huesped_id)
            : null;

        return view('reservas.create', compact('habitaciones', 'huespedes', 'huespedSeleccionado'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'huesped_id'    => 'required|exists:huespedes,id',
            'habitacion_id' => 'required|exists:habitaciones,id',
            'fecha_entrada' => 'required|date|after_or_equal:today',
            'fecha_salida'  => 'required|date|after:fecha_entrada',
            'num_personas'  => 'required|integer|min:1',
            'origen'        => 'required|in:web,telefono,presencial,agencia',
            'descuento'     => 'nullable|numeric|min:0',
            'observaciones' => 'nullable|string',
        ]);

        $habitacion = Habitacion::findOrFail($data['habitacion_id']);

        // Verificar disponibilidad
        if (!$habitacion->estaDisponible($data['fecha_entrada'], $data['fecha_salida'])) {
            return back()->withInput()
                ->with('error', 'La habitación no está disponible para las fechas seleccionadas.');
        }

        $entrada   = Carbon::parse($data['fecha_entrada']);
        $salida    = Carbon::parse($data['fecha_salida']);
        $noches    = $entrada->diffInDays($salida);
        $precioNoche = $habitacion->tipoHabitacion->precio_base;
        $subtotal  = $precioNoche * $noches;
        $descuento = $data['descuento'] ?? 0;
        $total     = $subtotal - $descuento;

        DB::transaction(function () use ($data, $noches, $precioNoche, $subtotal, $descuento, $total) {
            Reserva::create(array_merge($data, [
                'codigo'      => Reserva::generarCodigo(),
                'user_id'     => auth()->id(),
                'precio_noche'=> $precioNoche,
                'num_noches'  => $noches,
                'subtotal'    => $subtotal,
                'descuento'   => $descuento,
                'total'       => $total,
                'estado'      => 'confirmada',
            ]));

            // Marcar habitación como reservada
            Habitacion::find($data['habitacion_id'])->update(['estado' => 'reservada']);
        });

        return redirect()->route('reservas.index')
            ->with('success', 'Reserva creada y confirmada exitosamente.');
    }

    public function show(Reserva $reserva)
    {
        $reserva->load(['huesped', 'habitacion.tipoHabitacion', 'usuario', 'factura.pagos', 'cargosAdicionales']);
        return view('reservas.show', compact('reserva'));
    }

    public function edit(Reserva $reserva)
    {
        if (!in_array($reserva->estado, ['pendiente', 'confirmada'])) {
            return back()->with('error', 'Solo se pueden editar reservas pendientes o confirmadas.');
        }
        $habitaciones = Habitacion::with('tipoHabitacion')->where('activa', true)->get();
        $huespedes    = Huesped::orderBy('apellido')->get();
        return view('reservas.edit', compact('reserva', 'habitaciones', 'huespedes'));
    }

    public function update(Request $request, Reserva $reserva)
    {
        $data = $request->validate([
            'fecha_entrada' => 'required|date',
            'fecha_salida'  => 'required|date|after:fecha_entrada',
            'num_personas'  => 'required|integer|min:1',
            'origen'        => 'required|in:web,telefono,presencial,agencia',
            'descuento'     => 'nullable|numeric|min:0',
            'observaciones' => 'nullable|string',
        ]);

        $noches    = Carbon::parse($data['fecha_entrada'])->diffInDays($data['fecha_salida']);
        $subtotal  = $reserva->precio_noche * $noches;
        $descuento = $data['descuento'] ?? 0;

        $reserva->update(array_merge($data, [
            'num_noches' => $noches,
            'subtotal'   => $subtotal,
            'descuento'  => $descuento,
            'total'      => $subtotal - $descuento,
        ]));

        return redirect()->route('reservas.show', $reserva)
            ->with('success', 'Reserva actualizada correctamente.');
    }

    public function checkin(Reserva $reserva)
    {
        if ($reserva->estado !== 'confirmada') {
            return back()->with('error', 'Solo se puede hacer check-in a reservas confirmadas.');
        }
        DB::transaction(function () use ($reserva) {
            $reserva->update([
                'estado'        => 'checkin',
                'fecha_checkin' => Carbon::today(),
            ]);
            $reserva->habitacion->update(['estado' => 'ocupada']);
        });

        return back()->with('success', "Check-in realizado para {$reserva->huesped->nombre_completo}.");
    }

    public function checkout(Reserva $reserva)
    {
        if ($reserva->estado !== 'checkin') {
            return back()->with('error', 'Solo se puede hacer check-out a reservas en estado check-in.');
        }
        DB::transaction(function () use ($reserva) {
            $reserva->update([
                'estado'          => 'checkout',
                'fecha_checkout'  => Carbon::today(),
            ]);
            $reserva->habitacion->update(['estado' => 'disponible']);
        });

        return redirect()->route('facturas.create', ['reserva_id' => $reserva->id])
            ->with('success', "Check-out realizado. Genera la factura para cerrar la estancia.");
    }

    public function cancelar(Request $request, Reserva $reserva)
    {
        if (in_array($reserva->estado, ['checkout', 'cancelada'])) {
            return back()->with('error', 'No se puede cancelar esta reserva.');
        }
        DB::transaction(function () use ($reserva) {
            $reserva->update(['estado' => 'cancelada']);
            if ($reserva->estado !== 'checkin') {
                $reserva->habitacion->update(['estado' => 'disponible']);
            }
        });

        return redirect()->route('reservas.index')
            ->with('success', 'Reserva cancelada.');
    }

    public function agregarCargo(Request $request, Reserva $reserva)
    {
        $data = $request->validate([
            'concepto'        => 'required|string|max:150',
            'categoria'       => 'required|in:restaurante,minibar,lavanderia,telefono,transporte,tours,spa,otros',
            'precio_unitario' => 'required|numeric|min:0',
            'cantidad'        => 'required|integer|min:1',
            'fecha'           => 'required|date',
            'observaciones'   => 'nullable|string',
        ]);

        $reserva->cargosAdicionales()->create($data);

        return back()->with('success', 'Cargo adicional registrado.');
    }

    /** Verificar disponibilidad AJAX */
    public function verificarDisponibilidad(Request $request)
    {
        $hab = Habitacion::with('tipoHabitacion')->find($request->habitacion_id);
        if (!$hab) return response()->json(['error' => 'Habitación no encontrada'], 404);

        $disponible = $hab->estaDisponible(
            $request->fecha_entrada,
            $request->fecha_salida,
            $request->excluir_reserva_id
        );

        $noches = Carbon::parse($request->fecha_entrada)->diffInDays($request->fecha_salida);

        return response()->json([
            'disponible'   => $disponible,
            'precio_noche' => $hab->tipoHabitacion->precio_base,
            'num_noches'   => $noches,
            'subtotal'     => $hab->tipoHabitacion->precio_base * $noches,
        ]);
    }
}
