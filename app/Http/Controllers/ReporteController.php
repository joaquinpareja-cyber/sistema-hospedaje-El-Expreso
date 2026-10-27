<?php

namespace App\Http\Controllers;

use App\Models\Reserva;
use App\Models\Factura;
use App\Models\Pago;
use App\Models\Habitacion;
use App\Models\Huesped;
use App\Models\Configuracion;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;

class ReporteController extends Controller
{
    public function index()
    {
        return view('reportes.index');
    }

    public function ocupacion(Request $request)
    {
        [$desde, $hasta] = $this->rango($request);

        $totalHabitaciones = Habitacion::where('activa', true)->count();
        $ocupacionDiaria   = $this->ocupacionDiaria($desde, $hasta, $totalHabitaciones);

        // Resumen
        $totalReservas     = Reserva::whereBetween('fecha_entrada', [$desde->toDateString(), $hasta->toDateString()])->count();
        $reservasCanceladas= Reserva::where('estado', 'cancelada')
            ->whereBetween('fecha_entrada', [$desde->toDateString(), $hasta->toDateString()])->count();
        $promOcupacion     = collect($ocupacionDiaria)->avg('porcentaje') ?? 0;

        return view('reportes.ocupacion', compact(
            'desde', 'hasta', 'ocupacionDiaria', 'totalReservas',
            'reservasCanceladas', 'promOcupacion', 'totalHabitaciones'
        ));
    }

    public function ingresos(Request $request)
    {
        [$desde, $hasta] = $this->rango($request);
        $fechas = [$desde->toDateString(), $hasta->toDateString()];

        // Ingresos por método de pago
        $ingresosPorMetodo = Pago::whereBetween('fecha_pago', $fechas)
            ->selectRaw('metodo_pago, SUM(monto) as total, COUNT(*) as cantidad')
            ->groupBy('metodo_pago')
            ->get();

        // Ingresos diarios
        $ingresosDiarios = Pago::whereBetween('fecha_pago', $fechas)
            ->selectRaw('DATE(fecha_pago) as dia, SUM(monto) as total')
            ->groupBy('dia')
            ->orderBy('dia')
            ->get();

        // Facturas del período
        $facturas = Factura::with(['huesped', 'reserva.habitacion'])
            ->whereBetween('fecha_emision', $fechas)
            ->latest()
            ->get();

        $totalIngresos = $ingresosDiarios->sum('total');
        $totalIVA      = $facturas->where('estado', '!=', 'anulada')->sum('iva');

        return view('reportes.ingresos', compact(
            'desde', 'hasta', 'ingresosPorMetodo', 'ingresosDiarios',
            'facturas', 'totalIngresos', 'totalIVA'
        ));
    }

    public function huespedes(Request $request)
    {
        [$desde, $hasta] = $this->rango($request);

        // Huéspedes por nacionalidad
        $porNacionalidad = Huesped::select('nacionalidad')
            ->selectRaw('COUNT(*) as total')
            ->whereNotNull('nacionalidad')
            ->groupBy('nacionalidad')
            ->orderByDesc('total')
            ->take(10)
            ->get();

        // Huéspedes frecuentes
        $frecuentes = Huesped::withCount(['reservas as total_estancias' => function ($q) {
                $q->where('estado', 'checkout');
            }])
            ->having('total_estancias', '>', 1)
            ->orderByDesc('total_estancias')
            ->take(10)
            ->get();

        // Nuevos registros en el período
        $nuevosHuespedes = Huesped::whereBetween('created_at', [$desde, $hasta])->count();

        return view('reportes.huespedes', compact(
            'desde', 'hasta', 'porNacionalidad', 'frecuentes', 'nuevosHuespedes'
        ));
    }

    // ══════════════════════════════════════════════════════════════════
    //  EXPORTACIONES
    // ══════════════════════════════════════════════════════════════════

    public function exportarOcupacion(Request $request)
    {
        [$desde, $hasta] = $this->rango($request);
        $formato = $request->get('formato', 'pdf');

        $totalHabitaciones = Habitacion::where('activa', true)->count();
        $ocupacionDiaria   = $this->ocupacionDiaria($desde, $hasta, $totalHabitaciones);
        $promOcupacion  = collect($ocupacionDiaria)->avg('porcentaje') ?? 0;
        $totalReservas  = Reserva::whereBetween('fecha_entrada', [$desde->toDateString(), $hasta->toDateString()])->count();
        $empresa        = Configuracion::get('empresa_nombre', 'Sistema Hospedaje');

        if ($formato === 'excel') {
            return $this->exportarExcel(
                headers: ['Fecha', 'Hab. Ocupadas', 'Hab. Disponibles', '% Ocupación'],
                rows: array_map(fn($r) => [
                    Carbon::parse($r['fecha'])->format('d/m/Y'), $r['ocupadas'], $r['disponibles'], $r['porcentaje'] . '%',
                ], $ocupacionDiaria),
                filename: 'reporte_ocupacion_' . $desde->format('Ymd') . '_' . $hasta->format('Ymd')
            );
        }

        $pdf = Pdf::loadView('reportes.pdf.ocupacion', compact(
            'desde', 'hasta', 'ocupacionDiaria', 'promOcupacion',
            'totalReservas', 'totalHabitaciones', 'empresa'
        ))->setPaper('a4', 'portrait');

        return $pdf->download('reporte_ocupacion_' . $desde->format('Ymd') . '.pdf');
    }

    public function exportarIngresos(Request $request)
    {
        [$desde, $hasta] = $this->rango($request);
        $formato = $request->get('formato', 'pdf');

        $facturas      = Factura::with(['huesped', 'reserva.habitacion'])
            ->whereBetween('fecha_emision', [$desde->toDateString(), $hasta->toDateString()])
            ->orderBy('fecha_emision')
            ->get();
        $vigentes      = $facturas->where('estado', '!=', 'anulada');
        $totalIngresos = $vigentes->sum('total');
        $totalIVA      = $vigentes->sum('iva');
        $empresa       = Configuracion::get('empresa_nombre', 'Sistema Hospedaje');
        $moneda        = \App\Providers\AppServiceProvider::monedaSimbolo();

        if ($formato === 'excel') {
            return $this->exportarExcel(
                headers: ['N° Comprobante', 'Fecha', 'Huésped', 'Habitación', 'Subtotal', 'Descuento', 'IVA', 'Total', 'Estado'],
                rows: $facturas->map(fn($f) => [
                    $f->numero,
                    $f->fecha_emision->format('d/m/Y'),
                    $f->huesped->nombre_completo ?? '-',
                    'Hab. ' . ($f->reserva->habitacion->numero ?? '-'),
                    $moneda . ' ' . number_format($f->subtotal, 2),
                    $moneda . ' ' . number_format($f->descuento, 2),
                    $moneda . ' ' . number_format($f->iva, 2),
                    $moneda . ' ' . number_format($f->total, 2),
                    ucfirst($f->estado),
                ])->toArray(),
                filename: 'reporte_ingresos_' . $desde->format('Ymd') . '_' . $hasta->format('Ymd')
            );
        }

        $pdf = Pdf::loadView('reportes.pdf.ingresos', compact(
            'desde', 'hasta', 'facturas', 'totalIngresos', 'totalIVA', 'empresa', 'moneda'
        ))->setPaper('a4', 'landscape');

        return $pdf->download('reporte_ingresos_' . $desde->format('Ymd') . '.pdf');
    }

    public function exportarHuespedes(Request $request)
    {
        [$desde, $hasta] = $this->rango($request);
        $formato = $request->get('formato', 'pdf');

        $huespedes = Huesped::withCount(['reservas as total_reservas'])
            ->orderBy('nombre_completo')->get();
        $empresa   = Configuracion::get('empresa_nombre', 'Sistema Hospedaje');

        if ($formato === 'excel') {
            return $this->exportarExcel(
                headers: ['Nombre completo', 'Tipo Doc.', 'N° Documento', 'Nacionalidad', 'Procedencia', 'Teléfono', 'Reservas'],
                rows: $huespedes->map(fn($h) => [
                    $h->nombre_completo, $h->tipo_documento,
                    $h->num_documento, $h->nacionalidad ?? '-',
                    $h->procedencia ?? '-', $h->telefono ?? '-', $h->total_reservas,
                ])->toArray(),
                filename: 'reporte_huespedes_' . now()->format('Ymd')
            );
        }

        $pdf = Pdf::loadView('reportes.pdf.huespedes', compact(
            'desde', 'hasta', 'huespedes', 'empresa'
        ))->setPaper('a4', 'portrait');

        return $pdf->download('reporte_huespedes_' . now()->format('Ymd') . '.pdf');
    }

    // ══════════════════════════════════════════════════════════════════
    //  HELPERS
    // ══════════════════════════════════════════════════════════════════

    /** Rango [desde 00:00, hasta 23:59:59] del filtro; por defecto el mes actual, máximo 1 año. */
    private function rango(Request $request): array
    {
        $request->validate([
            'desde' => 'nullable|date',
            'hasta' => 'nullable|date|after_or_equal:desde',
        ]);

        $desde = $request->filled('desde') ? Carbon::parse($request->desde)->startOfDay() : Carbon::now()->startOfMonth();
        $hasta = $request->filled('hasta') ? Carbon::parse($request->hasta)->endOfDay()   : Carbon::now()->endOfMonth();

        if ($desde->diffInDays($hasta) > 366) {
            $hasta = $desde->copy()->addYear()->endOfDay();
        }

        return [$desde, $hasta];
    }

    /**
     * Habitaciones ocupadas por noche en el rango.
     * Una estancia ocupa desde la fecha real de check-in hasta la de check-out;
     * si el huésped sigue alojado, se proyecta hasta su fecha de salida
     * (o hasta hoy si ya la superó). Se resuelve con una sola consulta.
     */
    private function ocupacionDiaria(Carbon $desde, Carbon $hasta, int $totalHabitaciones): array
    {
        $manana = Carbon::tomorrow();

        $estancias = Reserva::whereIn('estado', ['checkin', 'checkout'])
            ->whereDate('fecha_entrada', '<=', $hasta)
            ->where(function ($q) use ($desde) {
                $q->whereDate('fecha_checkout', '>', $desde)
                  ->orWhereNull('fecha_checkout');
            })
            ->get(['fecha_entrada', 'fecha_salida', 'fecha_checkin', 'fecha_checkout'])
            ->map(function ($r) use ($manana) {
                $inicio = $r->fecha_checkin ?? $r->fecha_entrada;
                $fin    = $r->fecha_checkout ?? $r->fecha_salida->copy()->max($manana);
                return [$inicio->toDateString(), $fin->toDateString()];
            });

        $resultado = [];
        foreach (CarbonPeriod::create($desde->copy()->startOfDay(), $hasta->copy()->startOfDay()) as $dia) {
            $d = $dia->toDateString();
            $ocupadas = $estancias->filter(fn($e) => $e[0] <= $d && $e[1] > $d)->count();

            $resultado[] = [
                'fecha'       => $d,
                'label'       => $dia->format('d/m'),
                'ocupadas'    => $ocupadas,
                'disponibles' => max(0, $totalHabitaciones - $ocupadas),
                'porcentaje'  => $totalHabitaciones > 0 ? round(($ocupadas / $totalHabitaciones) * 100, 1) : 0,
            ];
        }

        return $resultado;
    }

    /** Genera descarga CSV compatible con Excel */
    private function exportarExcel(array $headers, array $rows, string $filename): \Illuminate\Http\Response
    {
        $celda = function ($v) {
            $v = (string) $v;
            // Evita inyección de fórmulas al abrir el archivo en Excel
            if ($v !== '' && in_array($v[0], ['=', '+', '-', '@'], true) && !is_numeric($v)) {
                $v = "'" . $v;
            }
            return '"' . str_replace('"', '""', $v) . '"';
        };

        $csv  = "\xEF\xBB\xBF"; // UTF-8 BOM para Excel
        $csv .= implode(';', array_map($celda, $headers)) . "\n";
        foreach ($rows as $row) {
            $csv .= implode(';', array_map($celda, $row)) . "\n";
        }

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename={$filename}.csv",
        ]);
    }
}
