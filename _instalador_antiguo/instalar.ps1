# ============================================================
#  SISTEMA DE HOSPEDAJE — Script de Instalacion PowerShell
#  Ejecutar con: .\instalar.ps1
# ============================================================

# NO usar Stop globalmente — causa falsos errores con native commands
$ErrorActionPreference = "Continue"
$scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
Set-Location $scriptDir

function Write-Step($n, $msg) {
    Write-Host ""
    Write-Host "[$n] $msg" -ForegroundColor Cyan
}
function Write-OK($msg)   { Write-Host "  OK: $msg" -ForegroundColor Green }
function Write-Warn($msg) { Write-Host "  AVISO: $msg" -ForegroundColor Yellow }
function Write-Fail($msg) {
    Write-Host ""
    Write-Host "  ERROR: $msg" -ForegroundColor Red
    Write-Host ""
    Read-Host "Presiona Enter para salir"
    exit 1
}

# Ejecutar composer (global o phar local) silenciando stderr informativo
function Run-Composer {
    param([string[]]$Arguments)
    $phar = Join-Path $scriptDir "composer.phar"
    if (Get-Command composer -ErrorAction SilentlyContinue) {
        & composer @Arguments
    } elseif (Test-Path $phar) {
        & php $phar @Arguments
    } else {
        Write-Fail "Composer no encontrado. Ejecuta primero el paso de instalacion de Composer."
    }
    return $LASTEXITCODE
}

Write-Host ""
Write-Host "============================================" -ForegroundColor Yellow
Write-Host "  SISTEMA DE HOSPEDAJE - Instalacion" -ForegroundColor Yellow
Write-Host "============================================" -ForegroundColor Yellow

# ------ 1. Verificar PHP ------
Write-Step "1/7" "Verificando PHP..."
$phpVer = & php -r "echo PHP_VERSION;" 2>$null
if (-not $phpVer) {
    Write-Fail "PHP no encontrado. Instala Laragon (https://laragon.org) o XAMPP (https://apachefriends.org)"
}
Write-OK "PHP $phpVer detectado"

# ------ 2. Verificar / Instalar Composer ------
Write-Step "2/7" "Verificando Composer..."

$composerOk = $false
$pharLocal  = Join-Path $scriptDir "composer.phar"

# Intento 1: composer global en PATH
$composerPath = Get-Command composer -ErrorAction SilentlyContinue
if ($composerPath) {
    # Verificar que realmente funciona (puede estar roto)
    $null = & composer --version 2>$null
    if ($LASTEXITCODE -eq 0) {
        $composerOk = $true
        Write-OK "Composer detectado en: $($composerPath.Source)"
    }
}

# Intento 2: composer.phar local
if (-not $composerOk -and (Test-Path $pharLocal)) {
    $null = & php $pharLocal --version 2>$null
    if ($LASTEXITCODE -eq 0) {
        $composerOk = $true
        Write-OK "Composer (phar) encontrado en directorio local"
    }
}

# Intento 3: descargar automaticamente con PHP
if (-not $composerOk) {
    Write-Warn "Composer no disponible. Descargando automaticamente..."
    try {
        $setupFile = Join-Path $scriptDir "composer-setup.php"

        # Descargar instalador oficial
        [Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12
        Invoke-WebRequest -Uri "https://getcomposer.org/installer" -OutFile $setupFile -UseBasicParsing

        # Ejecutar instalador
        & php $setupFile --filename=composer.phar --install-dir="$scriptDir" 2>&1 | Out-Null
        Remove-Item $setupFile -Force -ErrorAction SilentlyContinue

        if (Test-Path $pharLocal) {
            $composerOk = $true
            Write-OK "Composer descargado correctamente como composer.phar"
        } else {
            Write-Fail "No se pudo descargar Composer.`n  Instalalo manualmente desde https://getcomposer.org/download"
        }
    } catch {
        Write-Fail "Error descargando Composer: $_`n  Instalalo manualmente desde https://getcomposer.org/download"
    }
}

# ------ 3. Instalar Laravel en carpeta temporal ------
Write-Step "3/7" "Instalando Laravel 10 (puede tardar unos minutos)..."

$tempDir = Join-Path $scriptDir "_laravel_install"
if (Test-Path $tempDir) { Remove-Item $tempDir -Recurse -Force }

Run-Composer @("create-project", "laravel/laravel", $tempDir, "10.*", "--prefer-dist", "--no-interaction")
if (-not (Test-Path (Join-Path $tempDir "artisan"))) {
    Write-Fail "Fallo al instalar Laravel. Verifica tu conexion a internet."
}
Write-OK "Laravel 10 instalado"

# ------ 4. Mover archivos al directorio raiz ------
Write-Step "4/7" "Moviendo archivos Laravel al directorio principal..."

$excluir = @("hospedaje_src","instalar.bat","instalar.ps1","INSTRUCCIONES.txt","_laravel_install","composer.phar","composer-setup.php")

Get-ChildItem $tempDir | ForEach-Object {
    if ($excluir -notcontains $_.Name) {
        $destino = Join-Path $scriptDir $_.Name
        if (Test-Path $destino) { Remove-Item $destino -Recurse -Force }
        Move-Item $_.FullName $scriptDir -Force
    }
}
Remove-Item $tempDir -Recurse -Force -ErrorAction SilentlyContinue
Write-OK "Archivos base de Laravel listos"

# ------ 5. Configurar .env ------
Write-Step "5/7" "Configurando .env..."

$envSrc = Join-Path $scriptDir "hospedaje_src\.env.hospedaje"
$envDst = Join-Path $scriptDir ".env"
if (-not (Test-Path $envSrc)) { Write-Fail "No se encontro hospedaje_src\.env.hospedaje" }
Copy-Item $envSrc $envDst -Force
Write-OK ".env configurado (DB: hospedaje_db | host: localhost)"

& php artisan key:generate --force
Write-OK "APP_KEY generado"

# ------ 6. Copiar archivos del sistema ------
Write-Step "6/7" "Copiando archivos del Sistema de Hospedaje..."

$src = Join-Path $scriptDir "hospedaje_src"

# Models
$d = Join-Path $scriptDir "app\Models"
if (!(Test-Path $d)) { New-Item -ItemType Directory -Path $d | Out-Null }
Copy-Item "$src\app\Models\*" $d -Force
Write-OK "Models copiados (7 modelos)"

# Controllers
Copy-Item "$src\app\Http\Controllers\*" (Join-Path $scriptDir "app\Http\Controllers") -Force
Write-OK "Controllers copiados (6 controllers)"

# Migrations
Copy-Item "$src\database\migrations\*" (Join-Path $scriptDir "database\migrations") -Force
Write-OK "Migrations copiadas (7 tablas)"

# Seeders
Copy-Item "$src\database\seeders\*" (Join-Path $scriptDir "database\seeders") -Force
Write-OK "Seeders copiados"

# app/Models/User.php (actualizado con roles)
Copy-Item "$src\app\Models\User.php" (Join-Path $scriptDir "app\Models\User.php") -Force
Write-OK "User model actualizado con roles"

# Middleware AdminMiddleware
$middlewareDir = Join-Path $scriptDir "app\Http\Middleware"
Copy-Item "$src\app\Http\Middleware\AdminMiddleware.php" $middlewareDir -Force
Write-OK "Middleware AdminMiddleware copiado"

# Views
foreach ($vd in @("layouts","dashboard","habitaciones","huespedes","reservas","facturas","reportes","tipo_habitaciones","usuarios","calendario","auth")) {
    $dst = Join-Path $scriptDir "resources\views\$vd"
    if (!(Test-Path $dst)) { New-Item -ItemType Directory -Path $dst | Out-Null }
    $srcV = Join-Path $src "resources\views\$vd"
    if (Test-Path $srcV) { Copy-Item "$srcV\*" $dst -Force }
}
Write-OK "Vistas Blade copiadas (18 vistas)"

# Routes (primera copia)
Copy-Item "$src\routes\web.php" (Join-Path $scriptDir "routes\web.php") -Force
Write-OK "Rutas copiadas"

# ------ 7. Paquetes, auth y migraciones ------
Write-Step "7/7" "Instalando paquetes y ejecutando migraciones..."

Write-Host "  >> AdminLTE..." -ForegroundColor Gray
Run-Composer @("require", "jeroennoten/laravel-adminlte", "--no-interaction")

Write-Host "  >> DomPDF (generacion de PDFs)..." -ForegroundColor Gray
Run-Composer @("require", "barryvdh/laravel-dompdf", "--no-interaction")

Write-Host "  >> laravel/ui (autenticacion)..." -ForegroundColor Gray
Run-Composer @("require", "laravel/ui", "--no-interaction")
& php artisan ui bootstrap --auth --no-interaction 2>$null

# Re-copiar nuestras rutas (laravel/ui las sobreescribe)
Copy-Item "$src\routes\web.php" (Join-Path $scriptDir "routes\web.php") -Force
Write-OK "routes/web.php restaurado"

# Registrar middleware 'admin' en Kernel.php
$kernelPath = Join-Path $scriptDir "app\Http\Kernel.php"
$kernelContent = Get-Content $kernelPath -Raw
if ($kernelContent -notmatch "AdminMiddleware") {
    $kernelContent = $kernelContent -replace "(        'throttle'[^\r\n]+)", "`$1`r`n        'admin' => \App\Http\Middleware\AdminMiddleware::class,"
    Set-Content $kernelPath $kernelContent -NoNewline
    Write-OK "Middleware 'admin' registrado en Kernel.php"
} else {
    Write-OK "Middleware 'admin' ya estaba registrado"
}

Write-Host "  >> Assets de AdminLTE..." -ForegroundColor Gray
& php artisan adminlte:install --only=assets --force 2>$null

Write-Host "  >> Migraciones y seeders..." -ForegroundColor Gray
& php artisan migrate --seed --force 2>&1
if ($LASTEXITCODE -ne 0) {
    Write-Host ""
    Write-Warn "Las migraciones fallaron. Pasos para solucionarlo:"
    Write-Warn "  1. Abre XAMPP/Laragon y asegurate que MySQL este ACTIVO"
    Write-Warn "  2. Crea la base de datos: CREATE DATABASE hospedaje_db;"
    Write-Warn "  3. Ejecuta: php artisan migrate --seed"
} else {
    Write-OK "Base de datos creada con datos de ejemplo"
}

# ------ RESUMEN FINAL ------
Write-Host ""
Write-Host "============================================" -ForegroundColor Green
Write-Host "  INSTALACION COMPLETADA" -ForegroundColor Green
Write-Host "============================================" -ForegroundColor Green
Write-Host "  Inicia:  php artisan serve" -ForegroundColor White
Write-Host "  Abre:    http://localhost:8000" -ForegroundColor White
Write-Host "  Admin:   admin@hospedaje.com / password" -ForegroundColor White
Write-Host "============================================" -ForegroundColor Green
Write-Host ""
Read-Host "Presiona Enter para cerrar"
