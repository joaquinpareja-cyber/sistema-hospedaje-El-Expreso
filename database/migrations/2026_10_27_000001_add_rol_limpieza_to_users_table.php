<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Nuevo rol "limpieza": solo accede al panel de Limpieza */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY role ENUM('admin', 'recepcionista', 'supervisor', 'limpieza') NOT NULL DEFAULT 'recepcionista'");
    }

    public function down(): void
    {
        DB::table('users')->where('role', 'limpieza')->update(['role' => 'recepcionista', 'activo' => false]);
        DB::statement("ALTER TABLE users MODIFY role ENUM('admin', 'recepcionista', 'supervisor') NOT NULL DEFAULT 'recepcionista'");
    }
};
