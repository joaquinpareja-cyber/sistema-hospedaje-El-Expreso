<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentoUsuarioController;
use App\Http\Controllers\HabitacionController;
use App\Http\Controllers\TipoHabitacionController;
use App\Http\Controllers\HuespedController;
use App\Http\Controllers\ReservaController;
use App\Http\Controllers\RegistroDirectoController;
use App\Http\Controllers\FacturaController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\CalendarioController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\SistemaController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\HousekeepingController;
use App\Http\Controllers\TarifaTemporadaController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');
Route::redirect('/home', '/dashboard');

// El registro público está deshabilitado: los usuarios los crea un administrador.
Auth::routes(['register' => false, 'verify' => false]);

// ── Roles ─────────────────────────────────────────────────────
//  limpieza      : solo el panel de Limpieza y su perfil
//  recepcionista : operación diaria (reservas, huéspedes, cobros, housekeeping)
//  supervisor    : + habitaciones, tipos, tarifas, reportes, anular facturas
//  admin         : + usuarios, configuración, backup
Route::middleware(['auth', 'activo'])->group(function () {

    // ── Dashboard ──────────────────────────────────────────────
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ── Mi Perfil ──────────────────────────────────────────────
    Route::get('/perfil',             [PerfilController::class, 'index'])->name('perfil.index');
    Route::put('/perfil',             [PerfilController::class, 'update'])->name('perfil.update');
    Route::put('/perfil/password',    [PerfilController::class, 'updatePassword'])->name('perfil.password');
    Route::post('/perfil/avatar',     [PerfilController::class, 'updateAvatar'])->name('perfil.avatar');
    Route::delete('/perfil/avatar',   [PerfilController::class, 'deleteAvatar'])->name('perfil.avatar.delete');

    // ── Documentación del personal (archivos privados) ─────────
    Route::post('/perfil/documentos/{tipo}',      [DocumentoUsuarioController::class, 'subir'])->name('perfil.documentos.subir');
    Route::get('/documentos/{usuario}/{tipo}',    [DocumentoUsuarioController::class, 'ver'])->name('documentos.ver');

    // ── Limpieza (todos los roles, incluido personal de limpieza) ─
    Route::get('/housekeeping',                        [HousekeepingController::class, 'index'])->name('housekeeping.index');
    Route::post('/housekeeping/{habitacion}',          [HousekeepingController::class, 'actualizarEstado'])->name('housekeeping.estado');

    // ── Desde aquí: recepcionista o superior ──────────────────
    Route::middleware('admin:recepcionista')->group(function () {

        // ── Calendario ─────────────────────────────────────────────
        Route::get('/calendario',                [CalendarioController::class, 'index'])->name('calendario.index');
        Route::get('/calendario/eventos',        [CalendarioController::class, 'eventos'])->name('calendario.eventos');
        Route::get('/calendario/disponibilidad', [CalendarioController::class, 'disponibilidad'])->name('calendario.disponibilidad');

        // ── Huéspedes ──────────────────────────────────────────────
        Route::get('/huespedes',         [HuespedController::class, 'index'])->name('huespedes.index');
        Route::get('/huespedes/crear',   [HuespedController::class, 'create'])->name('huespedes.create');
        Route::post('/huespedes',        [HuespedController::class, 'store'])->name('huespedes.store');
        Route::get('/huespedes/buscar',  [HuespedController::class, 'buscarAjax'])->name('huespedes.buscar');
        Route::get('/huespedes/{huesped}',         [HuespedController::class, 'show'])->name('huespedes.show');
        Route::get('/huespedes/{huesped}/editar',  [HuespedController::class, 'edit'])->name('huespedes.edit');
        Route::put('/huespedes/{huesped}',         [HuespedController::class, 'update'])->name('huespedes.update');
        Route::delete('/huespedes/{huesped}',      [HuespedController::class, 'destroy'])->name('huespedes.destroy');

        // ── Registro directo (walk-in: llega sin reserva) ──────────
        Route::get('/registro-directo',  [RegistroDirectoController::class, 'create'])->name('registro-directo.create');
        Route::post('/registro-directo', [RegistroDirectoController::class, 'store'])->name('registro-directo.store');

        // ── Reservas ───────────────────────────────────────────────
        Route::get('/reservas',                  [ReservaController::class, 'index'])->name('reservas.index');
        Route::get('/reservas/crear',              [ReservaController::class, 'create'])->name('reservas.create');
        Route::post('/reservas',                   [ReservaController::class, 'store'])->name('reservas.store');
        Route::get('/reservas/disponibilidad',     [ReservaController::class, 'verificarDisponibilidad'])->name('reservas.disponibilidad');
        Route::get('/reservas/{reserva}',          [ReservaController::class, 'show'])->name('reservas.show');
        Route::get('/reservas/{reserva}/editar',   [ReservaController::class, 'edit'])->name('reservas.edit');
        Route::put('/reservas/{reserva}',          [ReservaController::class, 'update'])->name('reservas.update');
        Route::post('/reservas/{reserva}/checkin', [ReservaController::class, 'checkin'])->name('reservas.checkin');
        Route::post('/reservas/{reserva}/checkout',[ReservaController::class, 'checkout'])->name('reservas.checkout');
        Route::post('/reservas/{reserva}/cancelar',[ReservaController::class, 'cancelar'])->name('reservas.cancelar');
        Route::post('/reservas/{reserva}/cargos',  [ReservaController::class, 'agregarCargo'])->name('reservas.cargo');
        Route::post('/reservas/{reserva}/acompanantes', [ReservaController::class, 'agregarAcompanante'])->name('reservas.acompanantes.agregar');
        Route::delete('/reservas/{reserva}/acompanantes/{huesped}', [ReservaController::class, 'quitarAcompanante'])->name('reservas.acompanantes.quitar');

        // ── Facturación ────────────────────────────────────────────
        Route::get('/facturas',                    [FacturaController::class, 'index'])->name('facturas.index');
        Route::get('/facturas/crear',              [FacturaController::class, 'create'])->name('facturas.create');
        Route::post('/facturas',                   [FacturaController::class, 'store'])->name('facturas.store');
        Route::get('/facturas/{factura}',          [FacturaController::class, 'show'])->name('facturas.show');
        Route::get('/facturas/{factura}/pdf',      [FacturaController::class, 'pdf'])->name('facturas.pdf');
        Route::post('/facturas/{factura}/pago',    [FacturaController::class, 'registrarPago'])->name('facturas.pago');

        // ── Habitaciones (consulta y cambio de estado) ─────────────
        Route::get('/habitaciones',                     [HabitacionController::class, 'index'])->name('habitaciones.index');
        Route::post('/habitaciones/{habitacion}/estado',[HabitacionController::class, 'cambiarEstado'])->name('habitaciones.estado');

        // ── Supervisor y admin ─────────────────────────────────────
        Route::middleware('admin:supervisor')->group(function () {
            Route::post('/facturas/{factura}/anular',  [FacturaController::class, 'anular'])->name('facturas.anular');

            // Reportes
            Route::get('/reportes',            [ReporteController::class, 'index'])->name('reportes.index');
            Route::get('/reportes/ocupacion',  [ReporteController::class, 'ocupacion'])->name('reportes.ocupacion');
            Route::get('/reportes/ingresos',   [ReporteController::class, 'ingresos'])->name('reportes.ingresos');
            Route::get('/reportes/huespedes',  [ReporteController::class, 'huespedes'])->name('reportes.huespedes');
            Route::get('/reportes/ocupacion/exportar', [ReporteController::class, 'exportarOcupacion'])->name('reportes.ocupacion.exportar');
            Route::get('/reportes/ingresos/exportar',  [ReporteController::class, 'exportarIngresos'])->name('reportes.ingresos.exportar');
            Route::get('/reportes/huespedes/exportar', [ReporteController::class, 'exportarHuespedes'])->name('reportes.huespedes.exportar');

            // Habitaciones (gestión)
            Route::get('/habitaciones/crear',             [HabitacionController::class, 'create'])->name('habitaciones.create');
            Route::post('/habitaciones',                  [HabitacionController::class, 'store'])->name('habitaciones.store');
            Route::get('/habitaciones/{habitacion}/editar',[HabitacionController::class, 'edit'])->name('habitaciones.edit');
            Route::put('/habitaciones/{habitacion}',      [HabitacionController::class, 'update'])->name('habitaciones.update');
            Route::delete('/habitaciones/{habitacion}',   [HabitacionController::class, 'destroy'])->name('habitaciones.destroy');

            // Tipos de Habitación
            Route::get('/tipo-habitaciones',                      [TipoHabitacionController::class, 'index'])->name('tipo-habitaciones.index');
            Route::get('/tipo-habitaciones/crear',                [TipoHabitacionController::class, 'create'])->name('tipo-habitaciones.create');
            Route::post('/tipo-habitaciones',                     [TipoHabitacionController::class, 'store'])->name('tipo-habitaciones.store');
            Route::get('/tipo-habitaciones/{tipoHabitacion}/editar',[TipoHabitacionController::class, 'edit'])->name('tipo-habitaciones.edit');
            Route::put('/tipo-habitaciones/{tipoHabitacion}',     [TipoHabitacionController::class, 'update'])->name('tipo-habitaciones.update');
            Route::delete('/tipo-habitaciones/{tipoHabitacion}',  [TipoHabitacionController::class, 'destroy'])->name('tipo-habitaciones.destroy');
            Route::post('/tipo-habitaciones/{tipoHabitacion}/toggle',[TipoHabitacionController::class, 'toggleActivo'])->name('tipo-habitaciones.toggle');

            // Tarifas de temporada
            Route::get('/tarifas',                   [TarifaTemporadaController::class, 'index'])->name('tarifas.index');
            Route::get('/tarifas/crear',             [TarifaTemporadaController::class, 'create'])->name('tarifas.create');
            Route::post('/tarifas',                  [TarifaTemporadaController::class, 'store'])->name('tarifas.store');
            Route::get('/tarifas/{tarifa}/editar',   [TarifaTemporadaController::class, 'edit'])->name('tarifas.edit');
            Route::put('/tarifas/{tarifa}',          [TarifaTemporadaController::class, 'update'])->name('tarifas.update');
            Route::delete('/tarifas/{tarifa}',       [TarifaTemporadaController::class, 'destroy'])->name('tarifas.destroy');
            Route::post('/tarifas/{tarifa}/toggle',  [TarifaTemporadaController::class, 'toggle'])->name('tarifas.toggle');
        });

        // Va después de /habitaciones/crear para que "crear" no se tome como {habitacion}
        Route::get('/habitaciones/{habitacion}', [HabitacionController::class, 'show'])->name('habitaciones.show');

        // ── Usuarios, Configuración y Sistema (solo admin) ────────
        Route::middleware('admin')->group(function () {
            // Usuarios
            Route::get('/usuarios',                  [UserController::class, 'index'])->name('usuarios.index');
            Route::get('/usuarios/crear',            [UserController::class, 'create'])->name('usuarios.create');
            Route::post('/usuarios',                 [UserController::class, 'store'])->name('usuarios.store');
            Route::get('/usuarios/{usuario}/editar', [UserController::class, 'edit'])->name('usuarios.edit');
            Route::put('/usuarios/{usuario}',        [UserController::class, 'update'])->name('usuarios.update');
            Route::delete('/usuarios/{usuario}',     [UserController::class, 'destroy'])->name('usuarios.destroy');
            Route::post('/usuarios/{usuario}/toggle',[UserController::class, 'toggleActivo'])->name('usuarios.toggle');
            Route::delete('/usuarios/{usuario}/documentos/{tipo}', [DocumentoUsuarioController::class, 'eliminar'])->name('usuarios.documentos.eliminar');

            // Configuración
            Route::get('/configuracion',          [ConfiguracionController::class, 'index'])->name('configuracion.index');
            Route::put('/configuracion',          [ConfiguracionController::class, 'update'])->name('configuracion.update');
            Route::delete('/configuracion/logo',  [ConfiguracionController::class, 'eliminarLogo'])->name('configuracion.logo.delete');

            // Sistema / Backup
            Route::get('/sistema',                                        [SistemaController::class, 'index'])->name('sistema.index');
            Route::post('/sistema/backup',                                [SistemaController::class, 'crearBackup'])->name('sistema.backup');
            Route::get('/sistema/backup/{archivo}/descargar',             [SistemaController::class, 'descargarBackup'])->name('sistema.backup.descargar');
            Route::delete('/sistema/backup/{archivo}',                    [SistemaController::class, 'eliminarBackup'])->name('sistema.backup.eliminar');
            Route::post('/sistema/restaurar',                             [SistemaController::class, 'restaurar'])->name('sistema.restaurar');
            Route::post('/sistema/resetear',                              [SistemaController::class, 'resetear'])->name('sistema.resetear');
        });
    });
});
