<?php

namespace Database\Seeders;

use App\Models\Habitacion;
use Illuminate\Database\Seeder;

class HabitacionSeeder extends Seeder
{
    public function run(): void
    {
        $habitaciones = [
            // Piso 1
            ['numero' => '101', 'piso' => '1', 'tipo_habitacion_id' => 1, 'estado' => 'disponible'],
            ['numero' => '102', 'piso' => '1', 'tipo_habitacion_id' => 2, 'estado' => 'disponible'],
            ['numero' => '103', 'piso' => '1', 'tipo_habitacion_id' => 3, 'estado' => 'disponible'],
            ['numero' => '104', 'piso' => '1', 'tipo_habitacion_id' => 1, 'estado' => 'mantenimiento'],
            ['numero' => '105', 'piso' => '1', 'tipo_habitacion_id' => 2, 'estado' => 'disponible'],
            // Piso 2
            ['numero' => '201', 'piso' => '2', 'tipo_habitacion_id' => 3, 'estado' => 'disponible'],
            ['numero' => '202', 'piso' => '2', 'tipo_habitacion_id' => 4, 'estado' => 'disponible'],
            ['numero' => '203', 'piso' => '2', 'tipo_habitacion_id' => 2, 'estado' => 'disponible'],
            ['numero' => '204', 'piso' => '2', 'tipo_habitacion_id' => 3, 'estado' => 'disponible'],
            // Piso 3 - Suites
            ['numero' => '301', 'piso' => '3', 'tipo_habitacion_id' => 5, 'estado' => 'disponible'],
            ['numero' => '302', 'piso' => '3', 'tipo_habitacion_id' => 5, 'estado' => 'disponible'],
            ['numero' => '303', 'piso' => '3', 'tipo_habitacion_id' => 6, 'estado' => 'disponible'],
        ];

        foreach ($habitaciones as $hab) {
            Habitacion::firstOrCreate(['numero' => $hab['numero']], $hab);
        }
    }
}
