<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('huespedes', function (Blueprint $table) {
            $table->string('procedencia', 100)->nullable()->after('nacionalidad');
            $table->string('profesion', 100)->nullable()->after('procedencia');
            $table->string('estado_civil', 20)->nullable()->after('profesion');
        });
    }

    public function down(): void
    {
        Schema::table('huespedes', function (Blueprint $table) {
            $table->dropColumn(['procedencia', 'profesion', 'estado_civil']);
        });
    }
};
