<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\GestionaEstancias;
use App\Models\Habitacion;
use App\Models\Huesped;
use App\Models\Reserva;
use App\Models\TarifaTemporada;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReservaController extends Controller
{
    use GestionaEstancias;

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
                  ->orWhereHas('huesped', fn($qh) => $qh->where(function ($w) use ($b) {
                      $w->where('nombre_completo', 'like', "%{$b}%")
                        ->orWhere('num_documento', 'like', "%{$b}%");
                  }));
            });
        }

        $reservas = $query->latest()->paginate(15)->withQueryString();

        return view('reservas.index', compact('reservas'));
    }

    public function create(Request $request)
    {
        $habitaciones = Habitacion::with('tipoHabitacion')
            ->where('activa', true)
            ->ordenNumerico()
            ->get();
        // El huésped se busca por AJAX; solo se precarga el seleccionado.
        $huespedSeleccionado = Huesped::find($request->old('huesped_id', $request->huesped_id));
        $acompanantesSeleccionados = Huesped::whereIn('id', (array) $request->old('acompanantes', []))->get();

        return view('reservas.create', compact('habitaciones', 'huespedSeleccionado', 'acompanantesSeleccionados'));
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
            'acompanantes'   => 'nullable|array',
            'acompanantes.*' => 'integer|distinct|different:huesped_id|exists:huespedes,id',
        ], [
            'acompanantes.*.distinct'  => 'Un acompañante está repetido.',
            'acompanantes.*.different' => 'El titular no puede figurar también como acompañante.',
        ]);

        $acompanantes = $data['acompanantes'] ?? [];
        unset($data['acompanantes']);
        // El titular más sus acompañantes: num_personas nunca puede ser menor
        $data['num_personas'] = max((int) $data['num_personas'], 1 + count($acompanantes));

        $reserva = DB::transaction(function () use ($data, $acompanantes) {
            // Bloquea la habitación para que dos recepcionistas no la reserven a la vez
            $habitacion = Habitacion::with('tipoHabitacion')->lockForUpdate()->findOrFail($data['habitacion_id']);

            $this->validarHabitacion($habitacion, $data);
            $montos = $this->calcularMontos($habitacion, $data);

            $reserva = Reserva::create(array_merge($data, $montos, [
                'codigo'  => Reserva::generarCodigo(),
                'user_id' => auth()->id(),
                'estado'  => 'confirmada',
            ]));
            $reserva->acompanantes()->attach($acompanantes);

            $habitacion->recalcularEstado();

            return $reserva;
        });

        return redirect()->route('reservas.show', $reserva)
            ->with('success', "Reserva {$reserva->codigo} creada y confirmada exitosamente.");
    }

    public function show(Reserva $reserva)
    {
        $reserva->load(['huesped', 'acompanantes', 'habitacion.tipoHabitacion', 'usuario', 'factura.pagos', 'cargosAdicionales']);
        return view('reservas.show', compact('reserva'));
    }

    public function edit(Reserva $reserva)
    {
        if (!in_array($reserva->estado, ['pendiente', 'confirmada'])) {
            return back()->with('error', 'Solo se pueden editar reservas pendientes o confirmadas.');
        }
        $habitaciones = Habitacion::with('tipoHabitacion')->where('activa', true)->ordenNumerico()->get();
        return view('reservas.edit', compact('reserva', 'habitaciones'));
    }

    public function update(Request $request, Reserva $reserva)
    {
        if (!in_array($reserva->estado, ['pendiente', 'confirmada'])) {
            return back()->with('error', 'Solo se pueden editar reservas pendientes o confirmadas.');
        }

        $data = $request->validate([
            'habitacion_id' => 'required|exists:habitaciones,id',
            'fecha_entrada' => 'required|date',
            'fecha_salida'  => 'required|date|after:fecha_entrada',
            'num_personas'  => 'required|integer|min:1',
            'origen'        => 'required|in:web,telefono,presencial,agencia',
            'descuento'     => 'nullable|numeric|min:0',
            'observaciones' => 'nullable|string',
        ]);

        $minimo = 1 + $reserva->acompanantes()->count();
        if ($data['num_personas'] < $minimo) {
            throw ValidationException::withMessages([
                'num_personas' => "La reserva tiene {$minimo} persona(s) registradas (titular y acompañantes). Quita acompañantes antes de reducir el número.",
            ]);
        }

        DB::transaction(function () use ($reserva, $data) {
            $habitacion = Habitacion::with('tipoHabitacion')->lockForUpdate()->findOrFail($data['habitacion_id']);
            $habitacionAnterior = $reserva->habitacion;

            $this->validarHabitacion($habitacion, $data, $reserva->id);

            // Se recalcula el precio solo si cambian habitación o fechas;
            // así no se altera el precio pactado por un simple cambio de observaciones.
            $cambioEstancia = $habitacion->id !== $reserva->habitacion_id
                || $data['fecha_entrada'] !== $reserva->fecha_entrada->toDateString()
                || $data['fecha_salida']  !== $reserva->fecha_salida->toDateString();

            $montos = $cambioEstancia
                ? $this->calcularMontos($habitacion, $data)
                : $this->aplicarDescuento((float) $reserva->subtotal, $data);

            $reserva->update(array_merge($data, $montos));

            $habitacion->recalcularEstado();
            if ($habitacionAnterior && $habitacionAnterior->id !== $habitacion->id) {
                $habitacionAnterior->recalcularEstado();
            }
        });

        return redirect()->route('reservas.show', $reserva)
            ->with('success', 'Reserva actualizada correctamente.');
    }

    public function agregarAcompanante(Request $request, Reserva $reserva)
    {
        $request->validate(['huesped_id' => 'required|exists:huespedes,id']);

        $error = DB::transaction(function () use ($request, $reserva) {
            $reserva = Reserva::with('habitacion.tipoHabitacion')->lockForUpdate()->findOrFail($reserva->id);
            return $reserva->agregarAcompanante(Huesped::findOrFail($request->huesped_id));
        });

        return $error
            ? back()->with('error', $error)
            : back()->with('success', 'Acompañante agregado a la reserva.');
    }

    public function quitarAcompanante(Reserva $reserva, Huesped $huesped)
    {
        if (!$reserva->admiteCambiosDeAcompanantes()) {
            return back()->with('error', 'Esta reserva ya no admite cambios de acompañantes.');
        }

        $reserva->acompanantes()->detach($huesped->id);

        return back()->with('success', "{$huesped->nombre_completo} fue quitado de la reserva.");
    }

    public function checkin(Reserva $reserva)
    {
        if (!in_array($reserva->estado, ['pendiente', 'confirmada'])) {
            return back()->with('error', 'Solo se puede hacer check-in a reservas confirmadas.');
        }
        if ($reserva->fecha_entrada->isFuture()) {
            return back()->with('error', 'La fecha de entrada de esta reserva aún no llega. Modifica la reserva si el huésped llegó antes.');
        }
        if ($reserva->habitacion->reservas()->where('estado', 'checkin')->where('id', '!=', $reserva->id)->exists()) {
            return back()->with('error', "La habitación {$reserva->habitacion->numero} todavía está ocupada por otro huésped.");
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
            $habitacion = $reserva->habitacion;
            $habitacion->update([
                'estado_limpieza'      => 'sucia',
                'limpieza_actualizado' => now(),
                'limpieza_user_id'     => auth()->id(),
            ]);
            $habitacion->recalcularEstado();
        });

        if ($reserva->factura()->exists()) {
            return redirect()->route('facturas.show', $reserva->factura)
                ->with('success', 'Check-out realizado.');
        }

        return redirect()->route('facturas.create', ['reserva_id' => $reserva->id])
            ->with('success', "Check-out realizado. Genera la factura para cerrar la estancia.");
    }

    public function cancelar(Request $request, Reserva $reserva)
    {
        if (!in_array($reserva->estado, ['pendiente', 'confirmada'])) {
            $msg = $reserva->estado === 'checkin'
                ? 'El huésped ya está alojado: realiza el check-out en lugar de cancelar.'
                : 'No se puede cancelar esta reserva.';
            return back()->with('error', $msg);
        }

        DB::transaction(function () use ($reserva) {
            $reserva->update(['estado' => 'cancelada']);
            $reserva->habitacion->recalcularEstado();
        });

        return redirect()->route('reservas.index')
            ->with('success', 'Reserva cancelada.');
    }

    public function agregarCargo(Request $request, Reserva $reserva)
    {
        if (in_array($reserva->estado, ['cancelada', 'no_show'])) {
            return back()->with('error', 'No se pueden agregar cargos a una reserva cancelada.');
        }
        if ($reserva->factura()->exists()) {
            return back()->with('error', 'La reserva ya tiene un comprobante emitido. Anúlalo para poder agregar cargos.');
        }

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
        $request->validate([
            'habitacion_id'      => 'required|integer',
            'fecha_entrada'      => 'required|date',
            'fecha_salida'       => 'required|date|after:fecha_entrada',
            'excluir_reserva_id' => 'nullable|integer',
        ]);

        $hab = Habitacion::with('tipoHabitacion')->find($request->habitacion_id);
        if (!$hab) return response()->json(['error' => 'Habitación no encontrada'], 404);

        $disponible = $hab->estaDisponible(
            $request->fecha_entrada,
            $request->fecha_salida,
            $request->integer('excluir_reserva_id') ?: null
        );

        $estancia = TarifaTemporada::calcularEstancia(
            $hab->tipo_habitacion_id,
            $request->fecha_entrada,
            $request->fecha_salida,
            (float) $hab->tipoHabitacion->precio_base
        );

        return response()->json([
            'disponible'   => $disponible,
            'capacidad'    => $hab->tipoHabitacion->capacidad,
            'precio_noche' => $estancia['precio_noche'],
            'num_noches'   => $estancia['num_noches'],
            'subtotal'     => $estancia['subtotal'],
        ]);
    }

    /* ---- Helpers ---- */

    /** Valida que la habitación pueda alojar la reserva; lanza error de validación si no. */
}
