<?php

namespace App\Http\Controllers;

use App\Models\Habitacion;
use Illuminate\Http\Request;

class HousekeepingController extends Controller
{
    public const ESTADOS = [
        'sucia'       => ['label' => 'Sucia',          'corto' => 'Sucia',     'badge' => 'danger',  'icono' => 'fa-broom'],
        'en_limpieza' => ['label' => 'En limpieza',    'corto' => 'Limpiando', 'badge' => 'warning', 'icono' => 'fa-spray-can'],
        'inspeccion'  => ['label' => 'En inspección',  'corto' => 'Revisar',   'badge' => 'info',    'icono' => 'fa-search'],
        'limpia'      => ['label' => 'Limpia',         'corto' => 'Limpia',    'badge' => 'success', 'icono' => 'fa-check'],
    ];

    public function index(Request $request)
    {
        $query = Habitacion::with(['tipoHabitacion', 'reservas' => function ($q) {
            $q->where('estado', 'checkin')->with('huesped');
        }])->where('activa', true);

        if ($request->filled('estado_limpieza')) {
            $query->where('estado_limpieza', $request->estado_limpieza);
        }

        $habitaciones = $query->orderBy('piso')->ordenNumerico()->get()->groupBy('piso');

        $conteo  = Habitacion::where('activa', true)
            ->selectRaw('estado_limpieza, COUNT(*) as total')
            ->groupBy('estado_limpieza')
            ->pluck('total', 'estado_limpieza');
        $resumen = collect(self::ESTADOS)->map(fn($e, $k) => (int) ($conteo[$k] ?? 0))
            ->put('total', (int) $conteo->sum());
        $estados = self::ESTADOS;

        return view('housekeeping.index', compact('habitaciones', 'resumen', 'estados'));
    }

    public function actualizarEstado(Request $request, Habitacion $habitacion)
    {
        $request->validate([
            'estado_limpieza' => 'required|in:' . implode(',', array_keys(self::ESTADOS)),
            'notas'           => 'nullable|string|max:300',
        ]);

        $habitacion->update([
            'estado_limpieza'     => $request->estado_limpieza,
            'limpieza_actualizado'=> now(),
            'limpieza_notas'      => $request->notas,
            'limpieza_user_id'    => auth()->id(),
        ]);

        $label = self::ESTADOS[$request->estado_limpieza]['label'];

        if ($request->expectsJson()) {
            return response()->json([
                'ok'    => true,
                'estado'=> $request->estado_limpieza,
                'hora'  => now()->format('H:i'),
            ]);
        }

        return back()->with('success', "Hab. {$habitacion->numero} actualizada a: {$label}");
    }
}
