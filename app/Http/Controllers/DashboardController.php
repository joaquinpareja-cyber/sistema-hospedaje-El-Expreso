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
        // El personal de limpieza entra directo a su panel
        if (auth()->user()->isLimpieza()) {
            return redirect()->route('housekeeping.index');
        }

        Habitacion::sincronizarEstadosDelDia();

        $hoy = Carbon::today();
        $mesActual = Carbon::now()->startOfMonth();

        // Resumen de habitaciones (solo activas, en una consulta)
        $porEstado = Habitacion::where('activa', true)
            ->selectRaw('estado, COUNT(*) as total')
            ->groupBy('estado')
            ->pluck('total', 'estado');
        $totalHabitaciones   = (int) $porEstado->sum();
        $habitacionesOcupadas= (int) ($porEstado['ocupada'] ?? 0);
        $habitacionesDisp    = (int) ($porEstado['disponible'] ?? 0);
        $habitacionesMant    = (int) ($porEstado['mantenimiento'] ?? 0);
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

        // Totales generales
        $totalHuespedes   = Huesped::count();
        $reservasMes      = Reserva::whereBetween('fecha_entrada', [$mesActual->toDateString(), $mesActual->copy()->endOfMonth()->toDateString()])->count();
        $ingresosTotales  = Pago::sum('monto');

        // Reservas recientes
        $reservasRecientes = Reserva::with(['huesped', 'habitacion'])
            ->latest()
            ->take(8)
            ->get();

        // Gráfico: ocupación últimos 7 días (una consulta, agrupado en PHP)
        $inicioSemana = Carbon::today()->subDays(6);
        $estancias = Reserva::whereIn('estado', ['checkin', 'checkout'])
            ->whereDate('fecha_entrada', '<=', $hoy)
            ->where(function ($q) use ($inicioSemana) {
                $q->whereDate('fecha_checkout', '>', $inicioSemana)->orWhereNull('fecha_checkout');
            })
            ->get(['fecha_entrada', 'fecha_salida', 'fecha_checkin', 'fecha_checkout'])
            ->map(fn($r) => [
                ($r->fecha_checkin ?? $r->fecha_entrada)->toDateString(),
                ($r->fecha_checkout ?? $r->fecha_salida->copy()->max(Carbon::tomorrow()))->toDateString(),
            ]);

        $ocupacionSemanal = [];
        for ($i = 6; $i >= 0; $i--) {
            $dia = Carbon::today()->subDays($i);
            $d   = $dia->toDateString();
            $ocupacionSemanal[] = [
                'dia'     => $dia->locale('es')->isoFormat('ddd'),
                'fecha'   => $dia->format('d/m'),
                'ocupadas'=> $estancias->filter(fn($e) => $e[0] <= $d && $e[1] > $d)->count(),
            ];
        }

        // Gráfico: ingresos últimos 6 meses (una consulta)
        $inicioSeisMeses = Carbon::now()->subMonths(5)->startOfMonth();
        $totalesPorMes = Pago::whereDate('fecha_pago', '>=', $inicioSeisMeses)
            ->selectRaw("DATE_FORMAT(fecha_pago, '%Y-%m') as ym, SUM(monto) as total")
            ->groupBy('ym')
            ->pluck('total', 'ym');

        $ingresosMensuales = [];
        for ($i = 5; $i >= 0; $i--) {
            $mes = Carbon::now()->startOfMonth()->subMonths($i);
            $ingresosMensuales[] = [
                'mes'    => $mes->locale('es')->isoFormat('MMM YYYY'),
                'total'  => (float) ($totalesPorMes[$mes->format('Y-m')] ?? 0),
            ];
        }

        return view('dashboard.index', compact(
            'totalHabitaciones', 'habitacionesOcupadas', 'habitacionesDisp', 'habitacionesMant',
            'porcentajeOcupacion', 'checkinsHoy', 'checkoutsHoy', 'llegadasHoy', 'salidasHoy',
            'ingresosMes', 'facturasPendientes', 'reservasRecientes',
            'ocupacionSemanal', 'ingresosMensuales',
            'totalHuespedes', 'reservasMes', 'ingresosTotales'
        ));
    }
}
