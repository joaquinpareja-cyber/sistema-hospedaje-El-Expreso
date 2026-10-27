<?php

namespace App\Http\Controllers;

use App\Models\Reserva;
use App\Models\Factura;
use App\Models\Pago;
use App\Models\Habitacion;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReporteController extends Controller
{
    public function index()
    {
        return view('reportes.index');
    }

    public function ocupacion(Request $request)
    {
        $desde = $request->filled('desde') ? Carbon::parse($request->desde) : Carbon::now()->startOfMonth();
        $hasta = $request->filled('hasta') ? Carbon::parse($request->hasta) : Carbon::now()->endOfMonth();

        $totalHabitaciones = Habitacion::where('activa', true)->count();

        // Ocupación por día
        $ocupacionDiaria = [];
        $cursor = $desde->copy();
        while ($cursor <= $hasta) {
            $ocupadas = Reserva::where('estado', 'checkin')
                ->whereDate('fecha_checkin', '<=', $cursor)
                ->whereDate('fecha_checkout', '>', $cursor)
                ->count();
            $ocupacionDiaria[] = [
                'fecha'      => $cursor->format('Y-m-d'),
                'label'      => $cursor->format('d/m'),
                'ocupadas'   => $ocupadas,
                'porcentaje' => $totalHabitaciones > 0 ? round(($ocupadas / $totalHabitaciones) * 100, 1) : 0,
            ];
            $cursor->addDay();
        }

        // Resumen
        $totalReservas     = Reserva::whereBetween('fecha_entrada', [$desde, $hasta])->count();
        $reservasCanceladas= Reserva::where('estado', 'cancelada')
            ->whereBetween('fecha_entrada', [$desde, $hasta])->count();
        $promOcupacion     = collect($ocupacionDiaria)->avg('porcentaje');

        return view('reportes.ocupacion', compact(
            'desde', 'hasta', 'ocupacionDiaria', 'totalReservas',
            'reservasCanceladas', 'promOcupacion', 'totalHabitaciones'
        ));
    }

    public function ingresos(Request $request)
    {
        $desde = $request->filled('desde') ? Carbon::parse($request->desde) : Carbon::now()->startOfMonth();
        $hasta = $request->filled('hasta') ? Carbon::parse($request->hasta) : Carbon::now()->endOfMonth();

        // Ingresos por método de pago
        $ingresosPorMetodo = Pago::whereBetween('fecha_pago', [$desde, $hasta])
            ->selectRaw('metodo_pago, SUM(monto) as total, COUNT(*) as cantidad')
            ->groupBy('metodo_pago')
            ->get();

        // Ingresos diarios
        $ingresosDiarios = Pago::whereBetween('fecha_pago', [$desde, $hasta])
            ->selectRaw('DATE(fecha_pago) as dia, SUM(monto) as total')
            ->groupBy('dia')
            ->orderBy('dia')
            ->get();

        // Facturas del período
        $facturas = Factura::with(['huesped', 'reserva.habitacion'])
            ->whereBetween('fecha_emision', [$desde, $hasta])
            ->latest()
            ->get();

        $totalIngresos = $ingresosDiarios->sum('total');
        $totalIGV      = Factura::whereBetween('fecha_emision', [$desde, $hasta])->sum('igv');

        return view('reportes.ingresos', compact(
            'desde', 'hasta', 'ingresosPorMetodo', 'ingresosDiarios',
            'facturas', 'totalIngresos', 'totalIGV'
        ));
    }

    public function huespedes(Request $request)
    {
        $desde = $request->filled('desde') ? Carbon::parse($request->desde) : Carbon::now()->startOfMonth();
        $hasta = $request->filled('hasta') ? Carbon::parse($request->hasta) : Carbon::now()->endOfMonth();

        // Huéspedes por nacionalidad
        $porNacionalidad = \App\Models\Huesped::select('nacionalidad')
            ->selectRaw('COUNT(*) as total')
            ->whereNotNull('nacionalidad')
            ->groupBy('nacionalidad')
            ->orderByDesc('total')
            ->take(10)
            ->get();

        // Huéspedes frecuentes
        $frecuentes = \App\Models\Huesped::withCount(['reservas as total_estancias' => function ($q) {
                $q->where('estado', 'checkout');
            }])
            ->having('total_estancias', '>', 1)
            ->orderByDesc('total_estancias')
            ->take(10)
            ->get();

        // Nuevos registros en el período
        $nuevosHuespedes = \App\Models\Huesped::whereBetween('created_at', [$desde, $hasta])->count();

        return view('reportes.huespedes', compact(
            'desde', 'hasta', 'porNacionalidad', 'frecuentes', 'nuevosHuespedes'
        ));
    }
}
