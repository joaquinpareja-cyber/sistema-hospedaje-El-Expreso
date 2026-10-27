<?php

namespace Database\Seeders;

use App\Models\TipoHabitacion;
use Illuminate\Database\Seeder;

class TipoHabitacionSeeder extends Seeder
{
    public function run(): void
    {
        $tipos = [
            ['nombre' => 'Simple',        'descripcion' => 'Habitación con cama individual.',          'capacidad' => 1, 'precio_base' => 80.00],
            ['nombre' => 'Doble',         'descripcion' => 'Habitación con dos camas individuales.',   'capacidad' => 2, 'precio_base' => 120.00],
            ['nombre' => 'Matrimonial',   'descripcion' => 'Habitación con cama matrimonial.',         'capacidad' => 2, 'precio_base' => 140.00],
            ['nombre' => 'Triple',        'descripcion' => 'Habitación con tres camas.',               'capacidad' => 3, 'precio_base' => 160.00],
            ['nombre' => 'Suite',         'descripcion' => 'Suite de lujo con sala y jacuzzi.',        'capacidad' => 2, 'precio_base' => 280.00],
            ['nombre' => 'Suite Familiar','descripcion' => 'Suite amplia para familias.',              'capacidad' => 5, 'precio_base' => 320.00],
        ];

        foreach ($tipos as $tipo) {
            TipoHabitacion::firstOrCreate(['nombre' => $tipo['nombre']], $tipo);
        }
    }
}
