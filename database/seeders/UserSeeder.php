<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin: si ya existe solo actualiza rol/activo (preserva contraseña),
        // si no existe lo crea con contraseña por defecto
        $admin = User::where('email', 'admin@hospedaje.com')->first();
        if ($admin) {
            $admin->update(['role' => 'admin', 'activo' => true, 'name' => 'Administrador']);
        } else {
            User::create([
                'name'     => 'Administrador',
                'email'    => 'admin@hospedaje.com',
                'password' => Hash::make('password'),
                'role'     => 'admin',
                'activo'   => true,
            ]);
        }

        // Recepcionista
        $recep = User::where('email', 'recepcion@hospedaje.com')->first();
        if ($recep) {
            $recep->update(['role' => 'recepcionista', 'activo' => true, 'name' => 'Recepcionista']);
        } else {
            User::create([
                'name'     => 'Recepcionista',
                'email'    => 'recepcion@hospedaje.com',
                'password' => Hash::make('password'),
                'role'     => 'recepcionista',
                'activo'   => true,
            ]);
        }
    }
}
