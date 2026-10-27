@echo off
chcp 65001 > nul
echo ================================================
echo  SISTEMA DE HOSPEDAJE - Instalacion Laravel
echo ================================================
echo.

REM ---- Verificar PHP ----
php -v > nul 2>&1
IF %ERRORLEVEL% NEQ 0 (
    echo [ERROR] PHP no encontrado. Instala XAMPP o Laragon.
    pause & exit /b 1
)

REM ---- Verificar Composer ----
composer -V > nul 2>&1
IF %ERRORLEVEL% NEQ 0 (
    echo [ERROR] Composer no encontrado. Descarga desde https://getcomposer.org
    pause & exit /b 1
)

echo [OK] PHP y Composer detectados.
echo.

REM ---- Instalar Laravel en directorio actual ----
echo [1/6] Instalando Laravel 10...
composer create-project laravel/laravel . "10.*" --prefer-dist

echo.
echo [2/6] Instalando AdminLTE y paquetes adicionales...
composer require jeroennoten/laravel-adminlte
composer require barryvdh/laravel-dompdf
php artisan adminlte:install

echo.
echo [3/6] Copiando archivos del sistema...

REM Copiar archivos personalizados
xcopy /E /Y /I "hospedaje_src\app"          "app\"
xcopy /E /Y /I "hospedaje_src\database"     "database\"
xcopy /E /Y /I "hospedaje_src\resources"    "resources\"
xcopy /E /Y /I "hospedaje_src\routes"       "routes\"

echo.
echo [4/6] Configurando archivo .env...
copy /Y "hospedaje_src\.env.hospedaje" ".env"
php artisan key:generate

echo.
echo [5/6] Ejecutando migraciones y seeders...
php artisan migrate --seed

echo.
echo [6/6] Publicando assets de AdminLTE...
php artisan adminlte:install --only=assets

echo.
echo ================================================
echo  INSTALACION COMPLETADA
echo  Ejecuta: php artisan serve
echo  Abre:    http://localhost:8000
echo  Login:   admin@hospedaje.com / password
echo ================================================
pause
