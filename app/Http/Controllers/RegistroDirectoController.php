<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\GestionaEstancias;
use App\Models\Configuracion;
use App\Models\Factura;
use App\Models\Habitacion;
use App\Models\Huesped;
use App\Models\Pago;
use App\Models\Reserva;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Registro directo (walk-in): huésped que llega sin reserva.
 * En un solo paso registra al huésped, hace el check-in, emite el comprobante y cobra.
 */
class RegistroDirectoController extends Controller
{
    use GestionaEstancias;

    public function create(Request $request)
    {
        $hoy    = Carbon::today()->toDateString();
        $manana = Carbon::tomorrow()->toDateString();

        // Habitaciones libres desde hoy: sin mantenimiento, sin huésped dentro y sin reservas que se crucen
        $habitaciones = Habitacion::with('tipoHabitacion')
            ->where('activa', true)
            ->where('estado', '!=', 'mantenimiento')
            ->whereDoesntHave('reservas', fn($q) => $q->where(function ($w) use ($hoy, $manana) {
                $w->where('estado', 'checkin')
                  ->orWhere(fn($x) => $x->whereIn('estado', Habitacion::ESTADOS_RESERVA_ACTIVOS)
                      ->whereDate('fecha_entrada', '<', $manana)
                      ->whereDate('fecha_salida', '>', $hoy));
            }))
            ->ordenNumerico()
            ->get();

        // Se dibuja una ficha por cada persona posible (hasta la mayor capacidad);
        // el formulario muestra solo las que indica "Nro. de personas".
        $maxPersonas = max(1, (int) $habitaciones->max(fn($h) => $h->tipoHabitacion->capacidad));

        // Huéspedes ya registrados elegidos antes de un error de validación, para volver a mostrarlos
        $idsPrevios = collect($request->old('personas', []))->pluck('huesped_id')->filter();
        $huespedesPrevios = Huesped::whereIn('id', $idsPrevios)->get()->keyBy('id');

        $ivaPorcentaje = $this->ivaPorcentaje();

        return view('registro-directo.create', compact(
            'habitaciones', 'maxPersonas', 'huespedesPrevios', 'ivaPorcentaje'
        ));
    }

    public function store(Request $request)
    {
        $numPersonas = max(1, min(20, (int) $request->input('num_personas')));
        [$reglasPersonas, $nombresCampos] = $this->reglasPersonas($request, $numPersonas);

        $data = $request->validate(array_merge([
            'habitacion_id'   => 'required|exists:habitaciones,id',
            'num_noches'      => 'required|integer|min:1|max:90',
            'num_personas'    => 'required|integer|min:1|max:20',
            'descuento'       => 'nullable|numeric|min:0',
            'observaciones_reserva' => 'nullable|string',
            // Comprobante
            'tipo_comprobante'=> 'required|in:boleta,factura,recibo',
            'iva_aplicado'    => 'boolean',
            'nit_cliente'     => ['nullable', 'required_if:tipo_comprobante,factura', 'regex:/^\d{4,15}(-[0-9A-Za-z]{1,3})?$/'],
            'razon_social'    => 'nullable|required_if:tipo_comprobante,factura|string|max:150',
            // Pago (monto 0 = paga después, el comprobante queda pendiente)
            'monto_pagado'    => 'required|numeric|min:0',
            'metodo_pago'     => 'nullable|in:' . implode(',', array_keys(Pago::METODOS)),
            'referencia'      => 'nullable|string|max:100',
        ], $reglasPersonas), [
            'personas.*.huesped_id.required' => 'Busca y selecciona al huésped registrado.',
            'nit_cliente.required_if'  => 'Para emitir una factura se requiere el NIT o CI del cliente.',
            'nit_cliente.regex'        => 'El NIT/CI debe tener entre 4 y 15 dígitos (el CI puede llevar complemento, ej: 1234567-1B).',
            'razon_social.required_if' => 'Para emitir una factura se requiere la razón social.',
        ], $nombresCampos);

        if ((float) $data['monto_pagado'] > 0 && empty($data['metodo_pago'])) {
            throw ValidationException::withMessages(['metodo_pago' => 'Indica el método de pago.']);
        }

        // Solo las fichas visibles (1 = titular); las demás no se envían
        $personas = array_slice($data['personas'] ?? [], 0, $numPersonas, true);
        $this->validarPersonasRepetidas($personas);

        $estancia = [
            'fecha_entrada' => Carbon::today()->toDateString(),
            'fecha_salida'  => Carbon::today()->addDays((int) $data['num_noches'])->toDateString(),
            'num_personas'  => $numPersonas,
            'descuento'     => $data['descuento'] ?? 0,
        ];
        $ivaPorcentaje = $request->boolean('iva_aplicado') ? $this->ivaPorcentaje() : 0;

        $factura = DB::transaction(function () use ($data, $personas, $estancia, $ivaPorcentaje) {
            $habitacion = Habitacion::with('tipoHabitacion')->lockForUpdate()->findOrFail($data['habitacion_id']);

            if ($habitacion->estado === 'mantenimiento') {
                throw ValidationException::withMessages(['habitacion_id' => 'La habitación está en mantenimiento.']);
            }
            if ($habitacion->reservas()->where('estado', 'checkin')->exists()) {
                throw ValidationException::withMessages(['habitacion_id' => "La habitación {$habitacion->numero} todavía tiene un huésped dentro."]);
            }
            $this->validarHabitacion($habitacion, $estancia);
            $montos = $this->calcularMontos($habitacion, $estancia);

            // Crea a las personas nuevas y toma las ya registradas; la primera es el titular
            $huespedes = collect($personas)->map(fn($p) => $p['modo'] === 'existente'
                ? Huesped::findOrFail($p['huesped_id'])
                : Huesped::create(collect($p)->only((new Huesped)->getFillable())->all())
            )->values();
            $huesped = $huespedes->first();
            $acompanantes = $huespedes->slice(1)->pluck('id')->all();

            $reserva = Reserva::create(array_merge($estancia, $montos, [
                'codigo'        => Reserva::generarCodigo(),
                'huesped_id'    => $huesped->id,
                'habitacion_id' => $habitacion->id,
                'user_id'       => auth()->id(),
                'estado'        => 'checkin',
                'fecha_checkin' => Carbon::today(),
                'origen'        => 'presencial',
                'observaciones' => $data['observaciones_reserva'] ?? null,
            ]));
            $reserva->acompanantes()->attach($acompanantes);
            $habitacion->update(['estado' => 'ocupada']);

            $factura = Factura::emitirParaReserva($reserva, [
                'tipo_comprobante' => $data['tipo_comprobante'],
                'nit_cliente'      => $data['nit_cliente'] ?? null,
                'razon_social'     => $data['razon_social'] ?? null,
            ], $ivaPorcentaje);

            $monto = round((float) $data['monto_pagado'], 2);
            if ($monto > (float) $factura->total) {
                throw ValidationException::withMessages([
                    'monto_pagado' => 'El monto pagado no puede superar el total (' . number_format($factura->total, 2) . ').',
                ]);
            }
            if ($monto > 0) {
                Pago::create([
                    'factura_id'  => $factura->id,
                    'user_id'     => auth()->id(),
                    'monto'       => $monto,
                    'metodo_pago' => $data['metodo_pago'],
                    'referencia'  => $data['referencia'] ?? null,
                    'fecha_pago'  => Carbon::today(),
                ]);
                if ($monto >= (float) $factura->total) {
                    $factura->update(['estado' => 'pagada']);
                }
            }

            return $factura;
        });

        return redirect()->route('facturas.show', $factura)
            ->with('success', "Check-in realizado en la habitación {$factura->reserva->habitacion->numero}. Comprobante {$factura->numero} generado.")
            ->with('abrir_pdf', true);
    }

    /**
     * Reglas para personas[1..n]: cada una es un huésped ya registrado (huesped_id)
     * o uno nuevo con su ficha completa. Devuelve [reglas, nombres legibles de los campos].
     */
    private function reglasPersonas(Request $request, int $numPersonas): array
    {
        $reglas = ['personas' => 'required|array'];
        $nombres = [];

        for ($i = 1; $i <= $numPersonas; $i++) {
            $quien = $i === 1 ? 'titular' : "persona {$i}";
            $reglas["personas.{$i}.modo"] = 'required|in:nuevo,existente';

            if ($request->input("personas.{$i}.modo") === 'existente') {
                $reglas["personas.{$i}.huesped_id"] = 'required|exists:huespedes,id';
                $nombres["personas.{$i}.huesped_id"] = "huésped ({$quien})";
                continue;
            }

            foreach (Huesped::reglas() as $campo => $regla) {
                $reglas["personas.{$i}.{$campo}"] = $regla;
                $nombres["personas.{$i}.{$campo}"] = str_replace('_', ' ', $campo) . " ({$quien})";
            }
        }

        return [$reglas, $nombres];
    }

    /** La misma persona no puede ocupar dos fichas (mismo huésped o mismo documento) */
    private function validarPersonasRepetidas(array $personas): void
    {
        $vistos = [];
        foreach ($personas as $i => $p) {
            [$llave, $campo] = $p['modo'] === 'existente'
                ? ['id:' . $p['huesped_id'], "personas.{$i}.huesped_id"]
                : ['doc:' . mb_strtoupper(trim($p['num_documento'])), "personas.{$i}.num_documento"];

            if (isset($vistos[$llave])) {
                throw ValidationException::withMessages([$campo => "Esta persona ya está registrada en la ficha {$vistos[$llave]}."]);
            }
            $vistos[$llave] = $i;
        }

        // Un documento nuevo no puede coincidir con el de un huésped ya registrado elegido en otra ficha
        $idsExistentes = collect($personas)->where('modo', 'existente')->pluck('huesped_id');
        $docsExistentes = Huesped::whereIn('id', $idsExistentes)->pluck('num_documento')->map(fn($d) => mb_strtoupper(trim($d)));
        foreach ($personas as $i => $p) {
            if ($p['modo'] === 'nuevo' && $docsExistentes->contains(mb_strtoupper(trim($p['num_documento'])))) {
                throw ValidationException::withMessages(["personas.{$i}.num_documento" => 'Este documento pertenece a un huésped ya elegido en otra ficha.']);
            }
        }
    }

    private function ivaPorcentaje(): float
    {
        return (float) (Configuracion::get('facturacion_iva', 13) ?? 13);
    }
}
