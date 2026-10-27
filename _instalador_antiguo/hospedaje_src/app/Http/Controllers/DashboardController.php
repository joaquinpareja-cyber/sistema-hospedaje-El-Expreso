<?php

namespace App\Http\Controllers;

use App\Models\Habitacion;
use App\Models\Huesped;
use App\Models\Reserva;
use App\Models\Factura;
use App\Models\Pago;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $hoy = Carbon::today();
        $mesActual = Carbon::now()->startOfMonth();

        // Resumen de habitaciones
        $totalHabitaciones   = Habitacion::where('activa', true)->count();
        $habitacionesOcupadas= Habitacion::where('estado', 'ocupada')->count();
        $habitacionesDisp    = Habitacion::where('estado', 'disponible')->count();
        $habitacionesMant    = Habitacion::where('estado', 'mantenimiento')->count();
        $porcentajeOcupacion = $totalHabitaciones > 0
            ? round(($habitacionesOcupadas / $totalHabitaciones) * 100, 1)
            : 0;

        // Actividad de hoy
        $checkinsHoy   = Reserva::whereDate('fecha_checkin', $hoy)->count();
        $checkoutsHoy  = Reserva::whereDate('fecha_checkout', $hoy)->count();
        $llegadasHoy   = Reserva::whereDate('fecha_entrada', $hoy)
                            ->whereIn('estado', ['confirmada', 'pendiente'])->count();
        $salidasHoy    = Reserva::whereDate('fecha_salida', $hoy)
                            ->where('estado', 'checkin')->count();

        // Ingresos del mes
        $ingresosMes = Pago::whereDate('fecha_pago', '>=', $mesActual)->sum('monto');

        // Facturas pendientes
        $facturasPendientes = Factura::where('estado', 'pendiente')->count();

        // Reservas recientes
        $reservasRecientes = Reserva::with(['huesped', 'habitacion'])
            ->latest()
            ->take(8)
            ->get();

        // Gráfico: ocupación últimos 7 días
        $ocupacionSemanal = [];
        for ($i = 6; $i >= 0; $i--) {
            $dia = Carbon::today()->subDays($i);
            $ocupacionSemanal[] = [
                'dia'     => $dia->format('D'),
                'fecha'   => $dia->format('Y-m-d'),
                'ocupadas'=> Reserva::where('estado', 'checkin')
                                ->whereDate('fecha_checkin', '<=', $dia)
                                ->whereDate('fecha_checkout', '>', $dia)
                                ->count(),
            ];
        }

        // Gráfico: ingresos últimos 6 meses
        $ingresosMensuales = [];
        for ($i = 5; $i >= 0; $i--) {
            $mes = Carbon::now()->subMonths($i);
            $ingresosMensuales[] = [
                'mes'    => $mes->format('M Y'),
                'total'  => Pago::whereYear('fecha_pago', $mes->year)
                                ->whereMonth('fecha_pago', $mes->month)
                                ->sum('monto'),
            ];
        }

        return view('dashboard.index', compact(
            'totalHabitaciones', 'habitacionesOcupadas', 'habitacionesDisp', 'habitacionesMant',
            'porcentajeOcupacion', 'checkinsHoy', 'checkoutsHoy', 'llegadasHoy', 'salidasHoy',
            'ingresosMes', 'facturasPendientes', 'reservasRecientes',
            'ocupacionSemanal', 'ingresosMensuales'
        ));
    }
}
