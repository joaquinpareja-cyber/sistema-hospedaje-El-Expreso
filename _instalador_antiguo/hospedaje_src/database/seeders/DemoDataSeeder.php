<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = DB::table('users')->where('email', 'admin@hospedaje.com')->value('id') ?? 1;

        // ── Obtener habitaciones activas con sus precios ─────────────────
        $habs = DB::table('habitaciones')
            ->join('tipo_habitaciones', 'habitaciones.tipo_habitacion_id', '=', 'tipo_habitaciones.id')
            ->select('habitaciones.id', 'habitaciones.numero', 'tipo_habitaciones.precio_base')
            ->where('habitaciones.activa', true)
            ->get()
            ->keyBy('numero');

        // ── 20 Huéspedes ─────────────────────────────────────────────────
        $huespedes = [
            ['nombre' => 'Carlos',    'apellido' => 'Mendoza García',   'tipo_documento' => 'DNI', 'num_documento' => '43521678', 'genero' => 'M', 'telefono' => '987654321', 'email' => 'cmendoza@email.com',  'nacionalidad' => 'Peruana',    'fecha_nacimiento' => '1985-03-15'],
            ['nombre' => 'María',     'apellido' => 'López Torres',     'tipo_documento' => 'DNI', 'num_documento' => '52341890', 'genero' => 'F', 'telefono' => '976543210', 'email' => 'mlopez@email.com',    'nacionalidad' => 'Peruana',    'fecha_nacimiento' => '1990-07-22'],
            ['nombre' => 'José',      'apellido' => 'Quispe Mamani',    'tipo_documento' => 'DNI', 'num_documento' => '61234567', 'genero' => 'M', 'telefono' => '965432109', 'email' => 'jquispe@email.com',   'nacionalidad' => 'Peruana',    'fecha_nacimiento' => '1978-11-05'],
            ['nombre' => 'Ana',       'apellido' => 'García Flores',    'tipo_documento' => 'DNI', 'num_documento' => '47891234', 'genero' => 'F', 'telefono' => '954321098', 'email' => 'agarcia@email.com',   'nacionalidad' => 'Peruana',    'fecha_nacimiento' => '1993-04-18'],
            ['nombre' => 'Luis',      'apellido' => 'Fernández Castro', 'tipo_documento' => 'DNI', 'num_documento' => '55678901', 'genero' => 'M', 'telefono' => '943210987', 'email' => 'lfernandez@email.com','nacionalidad' => 'Peruana',    'fecha_nacimiento' => '1982-09-30'],
            ['nombre' => 'Rosa',      'apellido' => 'Rodríguez Vargas', 'tipo_documento' => 'DNI', 'num_documento' => '48123456', 'genero' => 'F', 'telefono' => '932109876', 'email' => 'rrodriguez@email.com','nacionalidad' => 'Peruana',    'fecha_nacimiento' => '1988-01-12'],
            ['nombre' => 'Jorge',     'apellido' => 'Ramírez Silva',    'tipo_documento' => 'DNI', 'num_documento' => '63456789', 'genero' => 'M', 'telefono' => '921098765', 'email' => 'jramirez@email.com',  'nacionalidad' => 'Colombiana', 'fecha_nacimiento' => '1975-06-25'],
            ['nombre' => 'Carmen',    'apellido' => 'Gutiérrez Mora',   'tipo_documento' => 'DNI', 'num_documento' => '51234567', 'genero' => 'F', 'telefono' => '910987654', 'email' => 'cgutierrez@email.com','nacionalidad' => 'Peruana',    'fecha_nacimiento' => '1995-12-08'],
            ['nombre' => 'Pedro',     'apellido' => 'Huanca Ticona',    'tipo_documento' => 'DNI', 'num_documento' => '72345678', 'genero' => 'M', 'telefono' => '909876543', 'email' => 'phuanca@email.com',   'nacionalidad' => 'Boliviana',  'fecha_nacimiento' => '1980-02-14'],
            ['nombre' => 'Elena',     'apellido' => 'Morales Díaz',     'tipo_documento' => 'DNI', 'num_documento' => '45678901', 'genero' => 'F', 'telefono' => '998765432', 'email' => 'emorales@email.com',  'nacionalidad' => 'Peruana',    'fecha_nacimiento' => '1987-08-03'],
            ['nombre' => 'Ricardo',   'apellido' => 'Santos Peña',      'tipo_documento' => 'DNI', 'num_documento' => '67890123', 'genero' => 'M', 'telefono' => '997654321', 'email' => 'rsantos@email.com',   'nacionalidad' => 'Peruana',    'fecha_nacimiento' => '1972-05-20'],
            ['nombre' => 'Lucía',     'apellido' => 'Vega Chávez',      'tipo_documento' => 'DNI', 'num_documento' => '53901234', 'genero' => 'F', 'telefono' => '996543210', 'email' => 'lvega@email.com',     'nacionalidad' => 'Chilena',    'fecha_nacimiento' => '1991-10-17'],
            ['nombre' => 'Miguel',    'apellido' => 'Torres Herrera',   'tipo_documento' => 'DNI', 'num_documento' => '44012345', 'genero' => 'M', 'telefono' => '995432109', 'email' => 'mtorres@email.com',   'nacionalidad' => 'Peruana',    'fecha_nacimiento' => '1983-03-28'],
            ['nombre' => 'Patricia',  'apellido' => 'Ruiz Cano',        'tipo_documento' => 'DNI', 'num_documento' => '58123456', 'genero' => 'F', 'telefono' => '994321098', 'email' => 'pruiz@email.com',     'nacionalidad' => 'Peruana',    'fecha_nacimiento' => '1997-07-09'],
            ['nombre' => 'Antonio',   'apellido' => 'Flores Luna',      'tipo_documento' => 'DNI', 'num_documento' => '71234567', 'genero' => 'M', 'telefono' => '993210987', 'email' => 'aflores@email.com',   'nacionalidad' => 'Ecuatoriana','fecha_nacimiento' => '1969-12-01'],
            ['nombre' => 'Sofía',     'apellido' => 'Castro Reyes',     'tipo_documento' => 'DNI', 'num_documento' => '46345678', 'genero' => 'F', 'telefono' => '992109876', 'email' => 'scastro@email.com',   'nacionalidad' => 'Peruana',    'fecha_nacimiento' => '1994-04-14'],
            ['nombre' => 'Fernando',  'apellido' => 'Díaz Alva',        'tipo_documento' => 'DNI', 'num_documento' => '65456789', 'genero' => 'M', 'telefono' => '991098765', 'email' => 'fdiaz@email.com',     'nacionalidad' => 'Argentina',  'fecha_nacimiento' => '1976-09-11'],
            ['nombre' => 'Isabel',    'apellido' => 'Rojas Paredes',    'tipo_documento' => 'DNI', 'num_documento' => '49567890', 'genero' => 'F', 'telefono' => '990987654', 'email' => 'irojas@email.com',    'nacionalidad' => 'Peruana',    'fecha_nacimiento' => '1989-06-23'],
            ['nombre' => 'Roberto',   'apellido' => 'Chávez León',      'tipo_documento' => 'DNI', 'num_documento' => '68678901', 'genero' => 'M', 'telefono' => '989876543', 'email' => 'rchavez@email.com',   'nacionalidad' => 'Peruana',    'fecha_nacimiento' => '1981-01-07'],
            ['nombre' => 'Daniela',   'apellido' => 'Medina Cruz',      'tipo_documento' => 'DNI', 'num_documento' => '57789012', 'genero' => 'F', 'telefono' => '988765432', 'email' => 'dmedina@email.com',   'nacionalidad' => 'Venezolana', 'fecha_nacimiento' => '1996-11-30'],
        ];

        $huespedesIds = [];
        foreach ($huespedes as $h) {
            $existing = DB::table('huespedes')->where('num_documento', $h['num_documento'])->value('id');
            if ($existing) {
                $huespedesIds[] = $existing;
            } else {
                $huespedesIds[] = DB::table('huespedes')->insertGetId(
                    array_merge($h, ['created_at' => now(), 'updated_at' => now()])
                );
            }
        }

        // ── 20 Reservas distribuidas en 6 meses ─────────────────────────
        // Formato: [huesped_idx, hab_numero, entrada, salida, estado, origen, personas]
        $reservasData = [
            // ── NOVIEMBRE 2025 ──────────────────────────────────────────
            [0,  '101', '2025-11-05', '2025-11-08', 'checkout',  'presencial', 1],  // R01: Simple 3n
            [1,  '202', '2025-11-15', '2025-11-19', 'checkout',  'telefono',   3],  // R02: Triple 4n

            // ── DICIEMBRE 2025 ──────────────────────────────────────────
            [2,  '105', '2025-12-03', '2025-12-07', 'checkout',  'agencia',    2],  // R03: Doble 4n
            [3,  '201', '2025-12-12', '2025-12-15', 'checkout',  'presencial', 2],  // R04: Matrimonial 3n
            [4,  '301', '2025-12-22', '2025-12-27', 'checkout',  'web',        2],  // R05: Suite 5n
            [5,  '103', '2025-12-27', '2025-12-30', 'checkout',  'telefono',   2],  // R06: Matrimonial 3n

            // ── ENERO 2026 ──────────────────────────────────────────────
            [6,  '203', '2026-01-08', '2026-01-11', 'checkout',  'presencial', 2],  // R07: Doble 3n
            [7,  '202', '2026-01-18', '2026-01-22', 'checkout',  'agencia',    3],  // R08: Triple 4n
            [8,  '204', '2026-01-25', '2026-01-28', 'checkout',  'web',        2],  // R09: Matrimonial 3n

            // ── FEBRERO 2026 ────────────────────────────────────────────
            [9,  '302', '2026-02-05', '2026-02-09', 'checkout',  'agencia',    2],  // R10: Suite 4n
            [10, '101', '2026-02-15', '2026-02-18', 'checkout',  'presencial', 1],  // R11: Simple 3n
            [11, '105', '2026-02-22', '2026-02-25', 'checkout',  'telefono',   2],  // R12: Doble 3n

            // ── MARZO 2026 ──────────────────────────────────────────────
            [12, '303', '2026-03-08', '2026-03-12', 'checkout',  'web',        4],  // R13: Suite Familiar 4n
            [13, '201', '2026-03-20', '2026-03-23', 'checkout',  'presencial', 2],  // R14: Matrimonial 3n
            [14, '202', '2026-03-28', '2026-04-01', 'checkout',  'agencia',    3],  // R15: Triple 4n

            // ── ABRIL 2026 ──────────────────────────────────────────────
            [15, '203', '2026-04-05', '2026-04-09', 'checkout',  'telefono',   2],  // R16: Doble 4n
            [16, '204', '2026-04-18', '2026-04-22', 'checkout',  'web',        2],  // R17: Matrimonial 4n
            [17, '101', '2026-04-26', '2026-05-01', 'checkout',  'presencial', 1],  // R18: Simple 5n (checkout HOY)

            // ── MAYO 2026 — ACTIVOS ─────────────────────────────────────
            [18, '102', '2026-04-29', '2026-05-04', 'checkin',   'presencial', 2],  // R19: Doble (activo)
            [19, '301', '2026-05-01', '2026-05-06', 'checkin',   'web',        2],  // R20: Suite (checkin HOY)
        ];

        $reservaIds = [];
        $contadorRes = DB::table('reservas')->count() + 1;

        foreach ($reservasData as $idx => $r) {
            [$hIdx, $habNumero, $entrada, $salida, $estado, $origen, $personas] = $r;

            if (!isset($habs[$habNumero])) continue;

            $hab         = $habs[$habNumero];
            $fechaEntrada= Carbon::parse($entrada);
            $fechaSalida = Carbon::parse($salida);
            $numNoches   = $fechaEntrada->diffInDays($fechaSalida);
            $precioNoche = $hab->precio_base;
            $subtotal    = $precioNoche * $numNoches;
            $total       = $subtotal;

            $anioRes     = $fechaEntrada->year;
            $codigo      = 'RES-' . $anioRes . '-' . str_pad($contadorRes, 4, '0', STR_PAD_LEFT);

            // Verificar si ya existe
            if (DB::table('reservas')->where('codigo', $codigo)->exists()) {
                $contadorRes++;
                $codigo = 'RES-' . $anioRes . '-' . str_pad($contadorRes, 4, '0', STR_PAD_LEFT);
            }

            $fechaCheckin  = null;
            $fechaCheckout = null;

            if ($estado === 'checkout') {
                $fechaCheckin  = $entrada;
                $fechaCheckout = $salida;
            } elseif ($estado === 'checkin') {
                $fechaCheckin = $entrada;
            }

            // R18: checkout real hoy
            if ($idx === 17) {
                $fechaCheckout = '2026-05-01';
            }
            // R20: checkin real hoy
            if ($idx === 19) {
                $fechaCheckin = '2026-05-01';
            }

            $reservaId = DB::table('reservas')->insertGetId([
                'codigo'         => $codigo,
                'huesped_id'     => $huespedesIds[$hIdx],
                'habitacion_id'  => $hab->id,
                'user_id'        => $adminId,
                'fecha_entrada'  => $entrada,
                'fecha_salida'   => $salida,
                'fecha_checkin'  => $fechaCheckin,
                'fecha_checkout' => $fechaCheckout,
                'num_personas'   => $personas,
                'estado'         => $estado,
                'precio_noche'   => $precioNoche,
                'num_noches'     => $numNoches,
                'subtotal'       => $subtotal,
                'descuento'      => 0,
                'total'          => $total,
                'origen'         => $origen,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);

            $reservaIds[$idx] = $reservaId;
            $contadorRes++;
        }

        // ── Actualizar estado de habitaciones con checkin activo ─────────
        foreach ($reservasData as $idx => $r) {
            if ($r[4] === 'checkin') {
                DB::table('habitaciones')
                    ->where('numero', $r[1])
                    ->update(['estado' => 'ocupada', 'updated_at' => now()]);
            }
        }

        // ── Facturas y Pagos ─────────────────────────────────────────────
        // Facturas para reservas checkout (pagadas) e checkin (pendientes)
        $contadorFac = DB::table('facturas')->count() + 1;
        $contadorPago = DB::table('pagos')->count() + 1;

        $metodos = ['efectivo', 'tarjeta_credito', 'tarjeta_debito', 'transferencia', 'yape', 'plin'];
        $comprobantes = ['boleta', 'boleta', 'boleta', 'factura', 'recibo'];

        foreach ($reservasData as $idx => $r) {
            if (!isset($reservaIds[$idx])) continue;
            $reservaId   = $reservaIds[$idx];
            $estado      = $r[4];
            $habNumero   = $r[1];
            $entrada     = $r[2];
            $hIdx        = $r[0];

            if (!in_array($estado, ['checkout', 'checkin'])) continue;
            if (!isset($habs[$habNumero])) continue;

            $hab       = $habs[$habNumero];
            $numNoches = Carbon::parse($r[2])->diffInDays(Carbon::parse($r[3]));
            $subtotal  = $hab->precio_base * $numNoches;
            $igv       = round($subtotal * 0.18, 2);
            $total     = $subtotal + $igv;

            $anioFac     = Carbon::parse($entrada)->year;
            $numFac      = 'FAC-' . $anioFac . '-' . str_pad($contadorFac, 4, '0', STR_PAD_LEFT);

            $estadoFac   = $estado === 'checkout' ? 'pagada' : 'pendiente';
            $comprobante = $comprobantes[array_rand($comprobantes)];
            $fechaEmision= $estado === 'checkout' ? $r[3] : now()->toDateString();

            $facturaId = DB::table('facturas')->insertGetId([
                'numero'          => $numFac,
                'reserva_id'      => $reservaId,
                'huesped_id'      => $huespedesIds[$hIdx],
                'user_id'         => $adminId,
                'fecha_emision'   => $fechaEmision,
                'subtotal'        => $subtotal,
                'igv'             => $igv,
                'descuento'       => 0,
                'total'           => $total,
                'estado'          => $estadoFac,
                'tipo_comprobante'=> $comprobante,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);

            $contadorFac++;

            // Pago para facturas pagadas
            if ($estadoFac === 'pagada') {
                DB::table('pagos')->insert([
                    'factura_id'  => $facturaId,
                    'user_id'     => $adminId,
                    'monto'       => $total,
                    'metodo_pago' => $metodos[array_rand($metodos)],
                    'referencia'  => 'OP-' . rand(100000, 999999),
                    'fecha_pago'  => $fechaEmision,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
                $contadorPago++;
            }
        }

        // ── Cargos adicionales en algunas reservas ───────────────────────
        $cargos = [
            // [reserva_idx, concepto, categoria, precio_unit, cantidad, fecha]
            [4,  'Desayuno buffet x2',   'restaurante',  35.00, 2, '2025-12-23'],
            [4,  'Minibar (bebidas)',     'minibar',      45.00, 1, '2025-12-24'],
            [5,  'Lavandería',           'lavanderia',   25.00, 1, '2025-12-28'],
            [9,  'Cena romántica',       'restaurante',  120.00,1, '2026-02-06'],
            [9,  'Spa pareja',           'spa',          180.00,1, '2026-02-07'],
            [12, 'Desayuno familiar x4', 'restaurante',  35.00, 4, '2026-03-09'],
            [12, 'Tour ciudad',          'tours',        80.00, 4, '2026-03-10'],
            [14, 'Transporte aeropuerto','transporte',   60.00, 1, '2026-03-28'],
            [18, 'Desayuno x2',          'restaurante',  30.00, 2, '2026-04-30'],
            [19, 'Minibar suite',        'minibar',      85.00, 1, '2026-05-01'],
        ];

        foreach ($cargos as $c) {
            [$rIdx, $concepto, $categoria, $precioUnit, $cantidad, $fecha] = $c;
            if (!isset($reservaIds[$rIdx])) continue;

            DB::table('cargos_adicionales')->insert([
                'reserva_id'      => $reservaIds[$rIdx],
                'factura_id'      => null,
                'concepto'        => $concepto,
                'categoria'       => $categoria,
                'precio_unitario' => $precioUnit,
                'cantidad'        => $cantidad,
                'subtotal'        => $precioUnit * $cantidad,
                'fecha'           => $fecha,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        }

        $this->command->info('✓ 20 Huéspedes creados');
        $this->command->info('✓ 20 Reservas (Nov 2025 – May 2026)');
        $this->command->info('✓ Facturas y pagos generados');
        $this->command->info('✓ Cargos adicionales agregados');
    }
}
