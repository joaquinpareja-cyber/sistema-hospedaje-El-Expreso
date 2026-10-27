<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class SistemaController extends Controller
{
    private string $backupDir = 'backups';

    // ── INDEX ──────────────────────────────────────────────────────────
    public function index()
    {
        Storage::makeDirectory($this->backupDir);

        $archivos = collect(Storage::files($this->backupDir))
            ->map(function ($path) {
                $name = basename($path);
                $size = Storage::size($path);
                $time = Storage::lastModified($path);
                return [
                    'nombre'   => $name,
                    'tamanio'  => $this->formatBytes($size),
                    'fecha'    => Carbon::createFromTimestamp($time)->format('d/m/Y H:i:s'),
                    'timestamp'=> $time,
                ];
            })
            ->sortByDesc('timestamp')
            ->values();

        // Info de la base de datos
        $dbName   = config('database.connections.mysql.database');
        $tablas   = count(DB::select('SHOW TABLES'));
        $dbSize   = $this->obtenerTamanioDb($dbName);
        $ultimoBackup = $archivos->first();

        return view('sistema.index', compact('archivos', 'dbName', 'tablas', 'dbSize', 'ultimoBackup'));
    }

    // ── CREAR BACKUP ───────────────────────────────────────────────────
    public function crearBackup()
    {
        try {
            Storage::makeDirectory($this->backupDir);

            $filename = 'backup_' . now()->format('Ymd_His') . '.sql';
            $sql = $this->generarSQL();

            Storage::put($this->backupDir . '/' . $filename, $sql);

            $tamanio = $this->formatBytes(strlen($sql));
            return back()->with('success', "✓ Backup creado: <strong>{$filename}</strong> ({$tamanio})");
        } catch (\Throwable $e) {
            return back()->with('error', 'Error al crear el backup: ' . $e->getMessage());
        }
    }

    // ── DESCARGAR BACKUP ───────────────────────────────────────────────
    public function descargarBackup(string $archivo)
    {
        $path = $this->backupDir . '/' . $archivo;
        if (!Storage::exists($path)) {
            return back()->with('error', 'Archivo no encontrado.');
        }
        return Storage::download($path, $archivo, ['Content-Type' => 'application/sql']);
    }

    // ── ELIMINAR BACKUP ────────────────────────────────────────────────
    public function eliminarBackup(string $archivo)
    {
        $path = $this->backupDir . '/' . $archivo;
        if (Storage::exists($path)) {
            Storage::delete($path);
        }
        return back()->with('success', 'Backup eliminado correctamente.');
    }

    // ── RESTAURAR ──────────────────────────────────────────────────────
    public function restaurar(Request $request)
    {
        $request->validate([
            'archivo_backup' => 'required|file|mimes:sql,txt|max:51200',
            'confirmacion'   => 'required|in:RESTAURAR',
        ], [
            'archivo_backup.required' => 'Selecciona un archivo de backup.',
            'archivo_backup.mimes'    => 'El archivo debe ser .sql o .txt.',
            'archivo_backup.max'      => 'El archivo no debe superar 50MB.',
            'confirmacion.in'         => 'Debes escribir exactamente RESTAURAR para confirmar.',
        ]);

        try {
            $sql = file_get_contents($request->file('archivo_backup')->getRealPath());
            $this->ejecutarSQL($sql);

            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('success', '✓ Sistema restaurado correctamente. Inicia sesión nuevamente.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Error al restaurar: ' . $e->getMessage());
        }
    }

    // ── RESETEAR SISTEMA ───────────────────────────────────────────────
    public function resetear(Request $request)
    {
        $request->validate([
            'confirmacion_reset' => 'required|in:RESETEAR',
            'password_admin'     => 'required',
        ], [
            'confirmacion_reset.in'       => 'Debes escribir exactamente RESETEAR para confirmar.',
            'password_admin.required'     => 'La contraseña de administrador es requerida.',
        ]);

        // Verificar contraseña del admin
        if (!\Hash::check($request->password_admin, auth()->user()->password)) {
            return back()->with('error', 'Contraseña de administrador incorrecta.');
        }

        try {
            // ── 1. Guardar datos que deben preservarse ─────────────────
            $usuarioActual = auth()->user();
            $usuariosAdmin = DB::table('users')
                ->whereIn('role', ['admin', 'supervisor'])
                ->get()
                ->map(fn($u) => (array)$u)
                ->toArray();

            // Guardar configuraciones actuales
            $configActual = [];
            if (DB::getSchemaBuilder()->hasTable('configuraciones')) {
                $configActual = DB::table('configuraciones')->get()
                    ->map(fn($c) => (array)$c)
                    ->toArray();
            }

            // ── 2. Limpiar solo datos operativos (NO tablas del sistema) ──
            // Eliminamos datos en orden para respetar FK
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            foreach ([
                'pagos', 'cargos_adicionales', 'facturas',
                'reservas', 'huespedes',
            ] as $tabla) {
                if (DB::getSchemaBuilder()->hasTable($tabla)) {
                    DB::table($tabla)->truncate();
                }
            }
            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            // ── 3. Restaurar usuarios admin preservando contraseñas ────
            // Asegurarse de que los admins guardados siguen existiendo
            foreach ($usuariosAdmin as $u) {
                DB::table('users')->updateOrInsert(
                    ['email' => $u['email']],
                    [
                        'name'       => $u['name'],
                        'password'   => $u['password'],   // contraseña original
                        'role'       => $u['role'],
                        'activo'     => $u['activo'] ?? true,
                        'telefono'   => $u['telefono'] ?? null,
                        'updated_at' => now(),
                    ]
                );
            }

            // ── 4. Restaurar configuraciones ────────────────────────────
            if (!empty($configActual)) {
                DB::table('configuraciones')->truncate();
                foreach ($configActual as $cfg) {
                    unset($cfg['id']);
                    $cfg['created_at'] = now();
                    $cfg['updated_at'] = now();
                    DB::table('configuraciones')->insert($cfg);
                }
            } else {
                // Si no había config, sembrar defaults
                Artisan::call('db:seed', ['--class' => 'ConfiguracionSeeder', '--force' => true]);
            }

            // ── 5. Resetear habitaciones al estado disponible ──────────
            DB::table('habitaciones')
                ->where('numero', '!=', '104') // mantener la que está en mantenimiento
                ->update(['estado' => 'disponible', 'updated_at' => now()]);

            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('success', '✓ Sistema reseteado. Los datos operativos fueron eliminados. Tus credenciales y configuración se mantienen.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Error al resetear: ' . $e->getMessage());
        }
    }

    // ══ HELPERS PRIVADOS ══════════════════════════════════════════════

    private function generarSQL(): string
    {
        $db   = config('database.connections.mysql.database');
        $sql  = "-- ========================================================\n";
        $sql .= "-- Sistema Hospedaje — Copia de Seguridad\n";
        $sql .= "-- Base de datos : {$db}\n";
        $sql .= "-- Generado      : " . now()->format('d/m/Y H:i:s') . "\n";
        $sql .= "-- Versión PHP   : " . PHP_VERSION . "\n";
        $sql .= "-- ========================================================\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS = 0;\n";
        $sql .= "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n";
        $sql .= "SET NAMES utf8mb4;\n\n";

        $tablas = DB::select('SHOW TABLES');
        foreach ($tablas as $tablaObj) {
            $tabla = array_values((array)$tablaObj)[0];

            // Estructura
            $create    = DB::select("SHOW CREATE TABLE `{$tabla}`");
            $createSql = array_values((array)$create[0])[1];

            $sql .= "-- --------------------------------------------------------\n";
            $sql .= "-- Tabla: `{$tabla}`\n";
            $sql .= "-- --------------------------------------------------------\n\n";
            $sql .= "DROP TABLE IF EXISTS `{$tabla}`;\n";
            $sql .= $createSql . ";\n\n";

            // Datos
            $filas = DB::table($tabla)->get();
            if ($filas->isEmpty()) continue;

            $columnas = '`' . implode('`, `', array_keys((array)$filas->first())) . '`';
            $bloques  = $filas->chunk(200);

            foreach ($bloques as $bloque) {
                $values = $bloque->map(function ($fila) {
                    $vals = array_map(function ($v) {
                        if ($v === null) return 'NULL';
                        return "'" . str_replace(
                            ['\\', "'",  "\n",  "\r",  "\x00", "\x1a"],
                            ['\\\\', "\\'", '\\n', '\\r', '\\0',  '\\Z'],
                            (string)$v
                        ) . "'";
                    }, (array)$fila);
                    return '(' . implode(', ', $vals) . ')';
                })->implode(",\n");

                $sql .= "INSERT INTO `{$tabla}` ({$col