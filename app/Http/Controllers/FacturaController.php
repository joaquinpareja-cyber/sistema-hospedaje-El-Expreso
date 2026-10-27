<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use App\Models\Factura;
use App\Models\Pago;
use App\Models\Reserva;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class FacturaController extends Controller
{
    public function index(Request $request)
    {
        $query = Factura::with(['huesped', 'reserva.habitacion'])->withSum('pagos', 'monto');

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }
        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_emision', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_emision', '<=', $request->fecha_hasta);
        }
        if ($request->filled('buscar')) {
            $b = $request->buscar;
            $query->where(function ($q) use ($b) {
                $q->where('numero', 'like', "%{$b}%")
                  ->orWhereHas('huesped', fn($qh) =>
                      $qh->where('nombre_completo', 'like', "%{$b}%"));
            });
        }

        $facturas = $query->latest()->paginate(15)->withQueryString();
        return view('facturas.index', compact('facturas'));
    }

    public function create(Request $request)
    {
        $reserva = null;
        if ($request->filled('reserva_id')) {
            $reserva = Reserva::with(['huesped', 'habitacion.tipoHabitacion', 'cargosAdicionales'])
                ->findOrFail($request->reserva_id);

            if ($reserva->factura) {
                return redirect()->route('facturas.show', $reserva->factura)
                    ->with('info', 'Esta reserva ya tiene una factura generada.');
            }
            if (!in_array($reserva->estado, ['checkin', 'checkout'])) {
                return redirect()->route('reservas.show', $reserva)
                    ->with('error', 'Solo se puede facturar una reserva con check-in o check-out.');
            }
        }
        $ivaPorcentaje = $this->ivaPorcentaje();
        return view('facturas.create', compact('reserva', 'ivaPorcentaje'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'reserva_id'      => 'required|exists:reservas,id',
            'tipo_comprobante'=> 'required|in:boleta,factura,recibo',
            'iva_aplicado'    => 'boolean',
            // NIT (solo dígitos) o CI con complemento opcional (p. ej. 1234567-1B)
            'nit_cliente'     => ['nullable', 'required_if:tipo_comprobante,factura', 'regex:/^\d{4,15}(-[0-9A-Za-z]{1,3})?$/'],
            'razon_social'    => 'nullable|required_if:tipo_comprobante,factura|string|max:150',
            'observaciones'   => 'nullable|string',
        ], [
            'nit_cliente.required_if'  => 'Para emitir una factura se requiere el NIT o CI del cliente.',
            'nit_cliente.regex'        => 'El NIT/CI debe tener entre 4 y 15 dígitos (el CI puede llevar complemento, ej: 1234567-1B).',
            'razon_social.required_if' => 'Para emitir una factura se requiere la razón social.',
        ]);

        $ivaPorcentaje = $this->ivaPorcentaje();
        $aplicarIva    = $request->boolean('iva_aplicado');

        $factura = DB::transaction(function () use ($data, $ivaPorcentaje, $aplicarIva) {
            // Bloquea la reserva para impedir dos comprobantes simultáneos
            $reserva = Reserva::with('cargosAdicionales')->lockForUpdate()->findOrFail($data['reserva_id']);

            if (!in_array($reserva->estado, ['checkin', 'checkout'])) {
                return null;
            }
            if ($reserva->factura()->exists()) {
                return $reserva->factura;
            }

            return Factura::emitirParaReserva($reserva, $data, $aplicarIva ? $ivaPorcentaje : 0);
        });

        if (!$factura) {
            return back()->withInput()->with('error', 'Solo se puede facturar una reserva con check-in o check-out.');
        }
        if (!$factura->wasRecentlyCreated) {
            return redirect()->route('facturas.show', $factura)
                ->with('info', 'Esta reserva ya tenía un comprobante generado.');
        }

        return redirect()->route('facturas.show', $factura)
            ->with('success', "Comprobante {$factura->numero} generado correctamente.");
    }

    public function show(Factura $factura)
    {
        $factura->load(['huesped', 'reserva.habitacion.tipoHabitacion', 'pagos.usuario',
                        'reserva.cargosAdicionales']);
        return view('facturas.show', compact('factura'));
    }

    public function registrarPago(Request $request, Factura $factura)
    {
        if ($factura->estado !== 'pendiente') {
            return back()->with('error', "No se pueden registrar pagos en un comprobante {$factura->estado}.");
        }

        $data = $request->validate([
            'monto'       => 'required|numeric|min:0.01|max:' . $factura->saldo_pendiente,
            'metodo_pago' => 'required|in:' . implode(',', array_keys(Pago::METODOS)),
            'referencia'  => 'nullable|string|max:100',
            'fecha_pago'  => 'required|date|before_or_equal:today',
            'observaciones'=> 'nullable|string',
        ], [
            'monto.max' => 'El monto no puede superar el saldo pendiente (' . number_format($factura->saldo_pendiente, 2) . ').',
        ]);

        DB::transaction(function () use ($data, $factura) {
            $factura = Factura::lockForUpdate()->findOrFail($factura->id);

            Pago::create(array_merge($data, [
                'factura_id' => $factura->id,
                'user_id'    => auth()->id(),
            ]));

            if ($factura->saldo_pendiente <= 0) {
                $factura->update(['estado' => 'pagada']);
            }
        });

        return back()->with('success', 'Pago registrado correctamente.');
    }

    public function anular(Factura $factura)
    {
        if ($factura->estado === 'pagada') {
            return back()->with('error', 'No se puede anular una factura pagada.');
        }
        if ($factura->estado === 'anulada') {
            return back()->with('info', 'El comprobante ya estaba anulado.');
        }
        if ($factura->pagos()->exists()) {
            return back()->with('error', 'El comprobante tiene pagos registrados; no puede anularse.');
        }

        DB::transaction(function () use ($factura) {
            $factura->update(['estado' => 'anulada']);
            // Liberar los cargos para que entren en el nuevo comprobante
            $factura->reserva->cargosAdicionales()
                ->where('factura_id', $factura->id)
                ->update(['factura_id' => null]);
        });

        return back()->with('success', 'Factura anulada. Ya puedes emitir un nuevo comprobante para la reserva.');
    }

    public function pdf(Factura $factura)
    {
        $factura->load(['huesped', 'reserva.habitacion.tipoHabitacion', 'pagos',
                        'reserva.cargosAdicionales']);
        $pdf = Pdf::loadView('facturas.pdf', compact('factura'));
        return $pdf->download("Comprobante-{$factura->numero}.pdf");
    }

    private function ivaPorcentaje(): float
    {
        return (float) (Configuracion::get('facturacion_iva', 13) ?? 13);
    }
}
