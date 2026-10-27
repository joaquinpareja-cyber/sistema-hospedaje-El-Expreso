<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
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
            ->filter(fn($path) => str_ends_with($path, '.sql'))
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
            $filename = $this->generarBackup();
            $tamanio  = $this->formatBytes(Storage::size($this->backupDir . '/' . $filename));

            return back()->with('success', "✓ Backup creado: {$filename} ({$tamanio})");
        } catch (\Throwable $e) {
            Log::error('Error al crear backup', ['exception' => $e]);
            return back()->with('error', 'No se pudo crear el backup. Revisa el log del sistema para más detalles.');
        }
    }

    // ── DESCARGAR BACKUP ───────────────────────────────────────────────
    public function descargarBackup(string $archivo)
    {
        $this->validarNombreBackup($archivo);
        $path = $this->backupDir . '/' . $archivo;
        if (!Storage::exists($path)) {
            return back()->with('error', 'Archivo no encontrado.');
        }
        return Storage::download($path, $archivo, ['Content-Type' => 'application/sql']);
    }

    // ── ELIMINAR BACKUP ────────────────────────────────────────────────
    public function eliminarBackup(string $archivo)
    {
        $this->validarNombreBackup($archivo);
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

        $sql = file_get_contents($request->file('archivo_backup')->getRealPath());

        try {
            $sentencias = $this->validarSentenciasBackup($this->separarSentencias($sql));
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        try {
            // Copia automática del estado actual por si la restauración sale mal
            $respaldo = $this->generarBackup('pre_restauracion_');
            Log::info("Backup previo a restauración: {$respaldo}");

            $this->ejecutarSentencias($sentencias);

            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('success', '✓ Sistema restaurado correctamente. Inicia sesión nuevamente.');
        } catch (\Throwable $e) {
            Log::error('Error al restaurar backup', ['exception' => $e]);
            return back()->with('error', 'Error al restaurar. Antes de restaurar se guardó una copia (pre_restauracion_…) en la lista de backups. Revisa el log del sistema.');
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
        if (!Hash::check($request->password_admin, auth()->user()->password)) {
            return back()->with('error', 'Contraseña de administrador incorrecta.');
        }

        try {
            // Copia automática antes de borrar datos
            $respaldo = $this->generarBackup('pre_reseteo_');
            Log::info("Backup previo a reseteo: {$respaldo}");

            // ── 1. Limpiar solo datos operativos ──────────────────────
            // Usuarios, configuración, habitaciones, tipos y tarifas se conservan.
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            try {
                foreach ([
                    'pagos', 'cargos_adicionales', 'facturas',
                    'reservas', 'huespedes',
                ] as $tabla) {
                    if (DB::getSchemaBuilder()->hasTable($tabla)) {
                        DB::table($tabla)->truncate();
                    }
                }
            } finally {
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
            }

            // ── 2. Sembrar configuración por defecto si no existe ─────
            if (DB::table('configuraciones')->count() === 0) {
                Artisan::call('db:seed', ['--class' => 'ConfiguracionSeeder', '--force' => true]);
            }

            // ── 3. Liberar habitaciones (las de mantenimiento se respetan) ──
            DB::table('habitaciones')
                ->where('estado', '!=', 'mantenimiento')
                ->update(['estado' => 'disponible', 'updated_at' => now()]);

            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('success', '✓ Sistema reseteado. Los datos operativos fueron eliminados. Tus credenciales y configuración se mantienen.');
        } catch (\Throwable $e) {
            Log::error('Error al resetear el sistema', ['exception' => $e]);
            return back()->with('error', 'Error al resetear el sistema. Revisa el log del sistema.');
        }
    }

    // ══ HELPERS PRIVADOS ══════════════════════════════════════════════

    /** Solo nombres simples de archivo .sql (evita acceder a rutas arbitrarias). */
    private function validarNombreBackup(string $archivo): void
    {
        abort_unless(preg_match('/^[A-Za-z0-9_\-]+\.sql$/', $archivo), 404);
    }

    /**
     * Genera el backup escribiendo tabla por tabla en el archivo, sin cargar
     * toda la base de datos en memoria. Devuelve el nombre del archivo.
     */
    private function generarBackup(string $prefijo = 'backup_'): string
    {
        Storage::makeDirectory($this->backupDir);

        $filename = $prefijo . now()->format('Ymd_His') . '.sql';
        $path     = Storage::path($this->backupDir . '/' . $filename);
        $fh       = fopen($path, 'w');

        try {
            $db = config('database.connections.mysql.database');
            fwrite($fh, "-- ========================================================\n");
            fwrite($fh, "-- Sistema Hospedaje — Copia de Seguridad\n");
            fwrite($fh, "-- Base de datos : {$db}\n");
            fwrite($fh, "-- Generado      : " . now()->format('d/m/Y H:i:s') . "\n");
            fwrite($fh, "-- ========================================================\n\n");
            fwrite($fh, "SET FOREIGN_KEY_CHECKS = 0;\n");
            fwrite($fh, "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n");
            fwrite($fh, "SET NAMES utf8mb4;\n\n");

            $pdo = DB::connection()->getPdo();

            foreach (DB::select('SHOW TABLES') as $tablaObj) {
                $tabla = array_values((array) $tablaObj)[0];

                $create    = DB::select("SHOW CREATE TABLE `{$tabla}`");
                $createSql = array_values((array) $create[0])[1];

                fwrite($fh, "-- --------------------------------------------------------\n");
                fwrite($fh, "-- Tabla: `{$tabla}`\n");
                fwrite($fh, "-- --------------------------------------------------------\n\n");
                fwrite($fh, "DROP TABLE IF EXISTS `{$tabla}`;\n");
                fwrite($fh, $createSql . ";\n\n");

                $columnas = null;
                $lote     = [];
                foreach (DB::table($tabla)->cursor() as $fila) {
                    $fila = (array) $fila;
                    $columnas ??= '`' . implode('`, `', array_keys($fila)) . '`';
                    $lote[] = '(' . implode(', ', array_map(
                        fn($v) => $v === null ? 'NULL' : $pdo->quote((string) $v),
                        $fila
                    )) . ')';

                    if (count($lote) === 200) {
                        fwrite($fh, "INSERT INTO `{$tabla}` ({$columnas}) VALUES\n" . implode(",\n", $lote) . ";\n\n");
                        $lote = [];
                    }
                }
                if ($lote) {
                    fwrite($fh, "INSERT INTO `{$tabla}` ({$columnas}) VALUES\n" . implode(",\n", $lote) . ";\n\n");
                }
            }

            fwrite($fh, "SET FOREIGN_KEY_CHECKS = 1;\n");
        } catch (\Throwable $e) {
            fclose($fh);
            @unlink($path);
            throw $e;
        }

        fclose($fh);
        return $filename;
    }

    /** Divide el script en sentencias respetando cadenas y comentarios. */
    private function separarSentencias(string $sql): array
    {
        $sentencias = [];
        $stmt      = '';
        $inString  = false;
        $strChar   = '';
        $total     = strlen($sql);

        for ($i = 0; $i < $total; $i++) {
            $c = $sql[$i];

            if ($inString) {
                $stmt .= $c;
                if ($c === '\\') { $stmt .= $sql[++$i] ?? ''; continue; }
                if ($c === $strChar) $inString = false;
            } else {
                if ($c === "'" || $c === '"' || $c === '`') {
                    $inString = true;
                    $strChar  = $c;
                    $stmt    .= $c;
                } elseif ($c === ';') {
                    $limpio = trim($stmt);
                    if ($limpio !== '') {
                        $sentencias[] = $limpio;
                    }
                    $stmt = '';
                } elseif ($c === '-' && ($sql[$i + 1] ?? '') === '-') {
                    // Saltar comentario de línea
                    while ($i < $total && $sql[$i] !== "\n") $i++;
                } elseif ($c === '/' && ($sql[$i + 1] ?? '') === '*' && ($sql[$i + 2] ?? '') !== '!') {
                    // Saltar comentario de bloque (los /*! */ condicionales de mysqldump se conservan)
                    $fin = strpos($sql, '*/', $i + 2);
                    $i   = $fin === false ? $total : $fin + 1;
                } else {
                    $stmt .= $c;
                }
            }
        }

        if (trim($stmt) !== '') {
            $sentencias[] = trim($stmt);
        }

        return $sentencias;
    }

    /**
     * Solo se aceptan las sentencias que produce un backup (estructura y datos
     * de tablas de esta base). Un archivo manipulado no puede crear usuarios de
     * MySQL, leer o escribir archivos del servidor ni tocar otras bases.
     */
    private function validarSentenciasBackup(array $sentencias): array
    {
        if (empty($sentencias)) {
            throw new \InvalidArgumentException('El archivo no contiene sentencias SQL.');
        }

        $db        = preg_quote(config('database.connections.mysql.database'), '/');
        $aEjecutar = [];

        foreach ($sentencias as $n => $sentencia) {
            // /*!40101 SET ... */ de mysqldump/HeidiSQL: se valida su contenido
            if (preg_match('/^\/\*!\d+\s+(.*?)\s*\*\/$/s', $sentencia, $m)) {
                $sentencia = $m[1];
            }

            // CREATE DATABASE / USE de la misma base (HeidiSQL, mysqldump --databases): se omiten
            if (preg_match("/^(CREATE\s+DATABASE\s+(IF\s+NOT\s+EXISTS\s+)?`?{$db}`?(\s|$)|USE\s+`?{$db}`?$)/i", $sentencia)) {
                continue;
            }

            if (!$this->esSentenciaDeBackup($sentencia)) {
                throw new \InvalidArgumentException(
                    'El archivo no parece un backup válido: contiene una sentencia no permitida (#' . ($n + 1) . ': "'
                    . mb_strimwidth(preg_replace('/\s+/', ' ', $sentencia), 0, 70, '…') . '").'
                );
            }
            $aEjecutar[] = $sentencia;
        }

        return $aEjecutar;
    }

    private function esSentenciaDeBackup(string $s): bool
    {
        $tabla = '`?[A-Za-z0-9_]+`?';   // sin "base.tabla"

        if (preg_match('/^SET\s+(FOREIGN_KEY_CHECKS|UNIQUE_CHECKS|SQL_MODE|NAMES|TIME_ZONE|AUTOCOMMIT|SQL_NOTES|CHARACTER_SET_CLIENT|CHARACTER_SET_RESULTS|COLLATION_CONNECTION|@[A-Za-z0-9_]+)\b[^;]*$/i', $s)) {
            return !preg_match('/\b(GLOBAL|PASSWORD|PERSIST)\b/i', $s);
        }
        if (preg_match("/^DROP\s+TABLE\s+(IF\s+EXISTS\s+)?{$tabla}$/i", $s)) {
            return true;
        }
        if (preg_match("/^(DELETE\s+FROM\s+|TRUNCATE\s+(TABLE\s+)?){$tabla}$/i", $s)) {
            return true;   // HeidiSQL vacía cada tabla antes de insertar
        }
        if (preg_match("/^(LOCK\s+TABLES\s+{$tabla}\s+WRITE|UNLOCK\s+TABLES)$/i", $s)) {
            return true;
        }
        if (preg_match("/^ALTER\s+TABLE\s+{$tabla}\s+(DISABLE|ENABLE)\s+KEYS$/i", $s)) {
            return true;
        }
        if (preg_match("/^CREATE\s+TABLE\s+(IF\s+NOT\s+EXISTS\s+)?{$tabla}\s*\(/i", $s)) {
            return !preg_match('/\b(DATA|INDEX)\s+DIRECTORY\b/i', $s);
        }
        if (preg_match("/^INSERT\s+INTO\s+{$tabla}\s*(\([^)]*\))?\s*VALUES\s*(\(.*)$/is", $s, $m)) {
            // Los valores solo pueden ser literales: cadenas, números, NULL o hex.
            // Nada de subconsultas ni funciones (p. ej. LOAD_FILE()).
            $sinCadenas = preg_replace("/'(?:[^'\\\\]|\\\\.|'')*'/s", '', $m[2]);
            $resto      = preg_replace('/\b(NULL|_binary|0x[0-9a-f]+|-?\d+(\.\d+)?(e[-+]?\d+)?)\b/i', '', $sinCadenas);
            return (bool) preg_match('/^[\s(),\-]*$/', $resto);
        }

        return false;
    }

    private function ejecutarSentencias(array $sentencias): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        try {
            foreach ($sentencias as $sentencia) {
                DB::unprepared($sentencia);
            }
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS = 1');
        }
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1_048_576) return round($bytes / 1_048_576, 2) . ' MB';
        if ($bytes >= 1_024)    return round($bytes / 1_024, 1) . ' KB';
        return $bytes . ' B';
    }

    private function obtenerTamanioDb(string $dbName): string
    {
        try {
            $result = DB::selectOne(
                "SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size
                 FROM information_schema.TABLES
                 WHERE table_schema = ?",
                [$dbName]
            );
            return ($result->size ?? 0) . ' MB';
        } catch (\Throwable) {
            return 'N/D';
        }
    }
}
