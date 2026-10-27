<?php

namespace App\Http\Controllers;

use App\Models\Huesped;
use Illuminate\Http\Request;

class HuespedController extends Controller
{
    public function index(Request $request)
    {
        $query = Huesped::query();

        if ($request->filled('buscar')) {
            $b = $request->buscar;
            $query->where(function ($q) use ($b) {
                $q->where('nombre', 'like', "%{$b}%")
                  ->orWhere('apellido', 'like', "%{$b}%")
                  ->orWhere('num_documento', 'like', "%{$b}%")
                  ->orWhere('email', 'like', "%{$b}%");
            });
        }

        if ($request->filled('tipo_documento')) {
            $query->where('tipo_documento', $request->tipo_documento);
        }

        $huespedes = $query->orderBy('apellido')->paginate(15);

        return view('huespedes.index', compact('huespedes'));
    }

    public function create()
    {
        return view('huespedes.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'          => 'required|string|max:100',
            'apellido'        => 'required|string|max:100',
            'tipo_documento'  => 'required|in:DNI,Pasaporte,CE,RUC',
            'num_documento'   => 'required|string|max:20|unique:huespedes,num_documento',
            'nacionalidad'    => 'nullable|string|max:60',
            'fecha_nacimiento'=> 'nullable|date|before:today',
            'genero'          => 'nullable|in:M,F,Otro',
            'telefono'        => 'nullable|string|max:20',
            'email'           => 'nullable|email|max:120',
            'direccion'       => 'nullable|string',
            'observaciones'   => 'nullable|string',
        ]);

        $huesped = Huesped::create($data);

        return redirect()->route('huespedes.show', $huesped)
            ->with('success', "Huésped {$huesped->nombre_completo} registrado correctamente.");
    }

    public function show(Huesped $huesped)
    {
        $huesped->load(['reservas.habitacion', 'facturas']);
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
        $data = $request->validate([
            'nombre'          => 'required|string|max:100',
            'apellido'        => 'required|string|max:100',
            'tipo_documento'  => 'required|in:DNI,Pasaporte,CE,RUC',
            'num_documento'   => "required|string|max:20|unique:huespedes,num_documento,{$huesped->id}",
            'nacionalidad'    => 'nullable|string|max:60',
            'fecha_nacimiento'=> 'nullable|date|before:today',
            'genero'          => 'nullable|in:M,F,Otro',
            'telefono'        => 'nullable|string|max:20',
            'email'           => 'nullable|email|max:120',
            'direccion'       => 'nullable|string',
            'observaciones'   => 'nullable|string',
        ]);

        $huesped->update($data);

        return redirect()->route('huespedes.show', $huesped)
            ->with('success', 'Datos del huésped actualizados.');
    }

    public function destroy(Huesped $huesped)
    {
        if ($huesped->reservas()->whereIn('estado', ['checkin', 'confirmada'])->exists()) {
            return back()->with('error', 'No se puede eliminar un huésped con reservas activas.');
        }
        $huesped->delete();
        return redirect()->route('huespedes.index')
            ->with('success', 'Huésped eliminado del sistema.');
    }

    /** Búsqueda AJAX para el formulario de reservas */
    public function buscarAjax(Request $request)
    {
        $huespedes = Huesped::where('num_documento', 'like', "%{$request->q}%")
            ->orWhere('apellido', 'like', "%{$request->q}%")
            ->select('id', 'nombre', 'apellido', 'num_documento', 'tipo_documento')
            ->limit(10)
            ->get()
            ->map(fn($h) => [
                'id'    => $h->id,
                'text'  => "{$h->apellido}, {$h->nombre} ({$h->tipo_documento}: {$h->num_documento})",
            ]);

        return response()->json(['results' => $huespedes]);
    }
}
