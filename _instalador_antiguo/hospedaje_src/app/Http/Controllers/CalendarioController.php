<?php

namespace App\Http\Controllers;

use App\Models\Habitacion;
use App\Models\Reserva;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CalendarioController extends Controller
{
    public function index()
    {
        $habitaciones = Habitacion::with('tipoHabitacion')
            ->where('activa', true)
            ->orderBy('numero')
            ->get();

        return view('calendario.index', compact('habitaciones'));
    }

    /**
     * Devuelve reservas en formato FullCalendar (JSON)
     */
    public function eventos(Request $request)
    {
        $desde = $request->filled('start')
            ? Carbon::parse($request->start)->startOfDay()
            : Carbon::now()->startOfMonth();

        $hasta = $request->filled('end')
            ? Carbon::parse($request->end)->endOfDay()
            : Carbon::now()->endOfMonth();

        $query = Reserva::with(['huesped', 'habitacion.tipoHabitacion'])
            ->whereNotIn('estado', ['cancelada', 'no_show'])
            ->where(function ($q) use ($desde, $hasta) {
                $q->whereBetween('fecha_entrada', [$desde, $hasta])
                  ->orWhereBetween('fecha_salida', [$desde, $hasta])
                  ->orWhere(function ($q2) use ($desde, $hasta) {
                      $q2->where('fecha_entrada', '<=', $desde)
                         ->where('fecha_salida', '>=', $hasta);
                  });
            });

        // Filtro por habitación
        if ($request->filled('habitacion_id')) {
            $query->where('habitacion_id', $request->habitacion_id);
        }

        $colores = [
            'pendiente'  => ['bg' => '#6c757d', 'border' => '#6c757d'],
            'confirmada' => ['bg' => '#007bff', 'border' => '#0056b3'],
            'checkin'    => ['bg' => '#28a745', 'border' => '#1e7e34'],
            'checkout'   => ['bg' => '#17a2b8', 'border' => '#117a8b'],
        ];

        $eventos = $query->get()->map(function ($r) use ($colores) {
            $color = $colores[$r->estado] ?? ['bg' => '#adb5bd', 'border' => '#6c757d'];
            return [
                'id'              => $r->id,
                'title'           => "Hab.{$r->habitacion->numero} — {$r->huesped->apellido}",
                'start'           => $r->fecha_entrada->format('Y-m-d'),
                'end'             => $r->fecha_salida->addDay()->format('Y-m-d'), // FullCalendar end es exclusivo
                'backgroundColor' => $color['bg'],
                'borderColor'     => $color['border'],
                'textColor'       => '#fff',
                'extendedProps'   => [
                    'reserva_id'    => $r->id,
                    'codigo'        => $r->codigo,
                    'huesped'       => $r->huesped->nombre_completo,
                    'habitacion'    => "Hab. {$r->habitacion->numero} ({$r->habitacion->tipoHabitacion->nombre})",
                    'estado'        => ucfirst($r->estado),
                    'noches'        => $r->num_noches,
                    'total'         => 'S/ ' . number_format($r->total, 2),
                    'url_detalle'   => route('reservas.show', $r->id),
                ],
            ];
        });

        return response()->json($eventos);
    }

    /**
     * Vista de disponibilidad por habitación para un mes dado
     */
    public function disponibilidad(Request $request)
    {
        $mes  = $request->filled('mes')  ? (int)$request->mes  : now()->month;
        $anio = $request->filled('anio') ? (int)$request->anio : now()->year;

        $inicio = Carbon::createFromDate($anio, $mes, 1)->startOfDay();
        $fin    = $inicio->copy()->endOfMonth();
        $dias   = $inicio->diffInDays($fin) + 1;

        $habitaciones = Habitacion::with(['tipoHabitacion',
            'reservas' => function ($q) use ($inicio, $fin) {
                $q->whereNotIn('estado', ['cancelada', 'no_show'])
                  ->where(function ($q2) use ($inicio, $fin) {
                      $q2->whereBetween('fecha_entrada', [$inicio, $fin])
                         ->orWhereBetween('fecha_salida', [$inicio, $fin])
                         ->orWhere(function ($q3) use ($inicio, $fin) {
                             $q3->where('fecha_entrada', '<=', $inicio)
                                ->where('fecha_salida', '>=', $fin);
                         });
                  });
            }
        ])->where('activa', true)->orderBy('numero')->get();

        return view('calendario.disponibilidad', compact(
            'habitaciones', 'inicio', 'fin', 'dias', 'mes', 'anio'
        ));
    }
}
