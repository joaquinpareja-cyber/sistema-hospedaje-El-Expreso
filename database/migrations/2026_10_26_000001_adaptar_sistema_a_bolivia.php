<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Adapta el sistema a la normativa boliviana:
 *  - IGV → IVA (columna facturas.igv y clave de configuración)
 *  - RUC → NIT (facturas.ruc_cliente y empresa_ruc); el NIT/CI admite hasta 20 caracteres
 *  - Documentos de huésped: DNI → CI, RUC → NIT
 *  - Métodos de pago: Yape y Plin → QR
 */
return new class extends Migration
{
    public function up(): void
    {
        // Facturas: IVA y NIT del cliente
        DB::statement('ALTER TABLE facturas CHANGE igv iva DECIMAL(10,2) NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE facturas CHANGE ruc_cliente nit_cliente VARCHAR(20) NULL');

        // Huéspedes: tipos de documento bolivianos
        DB::statement("ALTER TABLE huespedes MODIFY tipo_documento ENUM('DNI','Pasaporte','CE','RUC','CI','NIT') NOT NULL DEFAULT 'DNI'");
        DB::table('huespedes')->where('tipo_documento', 'DNI')->update(['tipo_documento' => 'CI']);
        DB::table('huespedes')->where('tipo_documento', 'RUC')->update(['tipo_documento' => 'NIT']);
        DB::statement("ALTER TABLE huespedes MODIFY tipo_documento ENUM('CI','Pasaporte','CE','NIT') NOT NULL DEFAULT 'CI'");

        // Pagos: QR en lugar de Yape / Plin
        DB::statement("ALTER TABLE pagos MODIFY metodo_pago ENUM('efectivo','tarjeta_credito','tarjeta_debito','transferencia','yape','plin','qr','otro') NOT NULL DEFAULT 'efectivo'");
        DB::table('pagos')->whereIn('metodo_pago', ['yape', 'plin'])->update(['metodo_pago' => 'qr']);
        DB::statement("ALTER TABLE pagos MODIFY metodo_pago ENUM('efectivo','tarjeta_credito','tarjeta_debito','transferencia','qr','otro') NOT NULL DEFAULT 'efectivo'");

        // Configuración
        DB::table('configuraciones')->where('clave', 'empresa_ruc')
            ->update(['clave' => 'empresa_nit', 'descripcion' => 'NIT de la empresa']);
        DB::table('configuraciones')->where('clave', 'facturacion_igv')
            ->update(['clave' => 'facturacion_iva', 'descripcion' => 'Porcentaje de IVA (%)']);
    }

    public function down(): void
    {
        DB::table('configuraciones')->where('clave', 'empresa_nit')
            ->update(['clave' => 'empresa_ruc', 'descripcion' => 'RUC de la empresa (11 dígitos)']);
        DB::table('configuraciones')->where('clave', 'facturacion_iva')
            ->update(['clave' => 'facturacion_igv', 'descripcion' => 'Porcentaje de IGV / impuesto (%)']);

        DB::statement("ALTER TABLE pagos MODIFY metodo_pago ENUM('efectivo','tarjeta_credito','tarjeta_debito','transferencia','yape','plin','qr','otro') NOT NULL DEFAULT 'efectivo'");
        DB::table('pagos')->where('metodo_pago', 'qr')->update(['metodo_pago' => 'yape']);
        DB::statement("ALTER TABLE pagos MODIFY metodo_pago ENUM('efectivo','tarjeta_credito','tarjeta_debito','transferencia','yape','plin','otro') NOT NULL DEFAULT 'efectivo'");

        DB::statement("ALTER TABLE huespedes MODIFY tipo_documento ENUM('DNI','Pasaporte','CE','RUC','CI','NIT') NOT NULL DEFAULT 'CI'");
        DB::table('huespedes')->where('tipo_documento', 'CI')->update(['tipo_documento' => 'DNI']);
        DB::table('huespedes')->where('tipo_documento', 'NIT')->update(['tipo_documento' => 'RUC']);
        DB::statement("ALTER TABLE huespedes MODIFY tipo_documento ENUM('DNI','Pasaporte','CE','RUC') NOT NULL DEFAULT 'DNI'");

        DB::statement('ALTER TABLE facturas CHANGE nit_cliente ruc_cliente VARCHAR(11) NULL');
        DB::statement('ALTER TABLE facturas CHANGE iva igv DECIMAL(10,2) NOT NULL DEFAULT 0');
    }
};
