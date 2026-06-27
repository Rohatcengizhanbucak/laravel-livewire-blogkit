@echo off
setlocal EnableExtensions

cd /d "%~dp0"

set "HOST=127.0.0.1"
set "PORT=8000"
set "APP_URL=http://%HOST%:%PORT%"
set "PHP_EXE=php"
set "PNPM_EXE=pnpm"
set "NPM_EXE=npm"
set "CA_FILE=C:\xampp\php\extras\ssl\cacert.pem"
set "SERVER_RUNNING=0"

if exist "C:\xampp\php\php.exe" (
    set "PHP_EXE=C:\xampp\php\php.exe"
) else (
    where php >nul 2>nul
    if errorlevel 1 (
        echo PHP not found. Install PHP 8.3+ or check C:\xampp\php\php.exe.
        pause
        exit /b 1
    )
)

where pnpm >nul 2>nul
if errorlevel 1 set "PNPM_EXE="

where npm >nul 2>nul
if errorlevel 1 set "NPM_EXE="

if exist "%CA_FILE%" (
    set "NODE_EXTRA_CA_CERTS=%CA_FILE%"
)

if /I "%~1"=="check" (
    echo Project: %CD%
    "%PHP_EXE%" -v

    if not exist "vendor\autoload.php" (
        echo Missing: vendor\autoload.php
        exit /b 1
    )

    if not exist ".env" (
        echo Missing: .env
        exit /b 1
    )

    if not exist "public\build\manifest.json" (
        echo Missing: public\build\manifest.json
        exit /b 1
    )

    echo start.bat check passed.
    exit /b 0
)

if not exist "vendor\autoload.php" (
    echo Composer dependencies are missing. Run composer install first.
    pause
    exit /b 1
)

if not exist ".env" (
    copy ".env.example" ".env" >nul
    "%PHP_EXE%" artisan key:generate --ansi
)

if not exist "database\database.sqlite" (
    type nul > "database\database.sqlite"
)

"%PHP_EXE%" artisan migrate --graceful --ansi
if errorlevel 1 (
    pause
    exit /b 1
)

if not exist "public\build\manifest.json" (
    if not "%PNPM_EXE%"=="" (
        if not exist "node_modules" (
            if exist "%CA_FILE%" (
                %PNPM_EXE% install --config.cafile="%CA_FILE%"
            ) else (
                %PNPM_EXE% install
            )
        )

        %PNPM_EXE% run build
    ) else (
        if "%NPM_EXE%"=="" (
            echo Frontend build is missing and npm was not found.
            echo Install Node.js LTS, then run this file again.
            pause
            exit /b 1
        )

        if not exist "node_modules" %NPM_EXE% install
        %NPM_EXE% run build
    )
)

if /I "%~1"=="dev" (
    if not "%PNPM_EXE%"=="" (
        if not exist "node_modules" (
            if exist "%CA_FILE%" (
                %PNPM_EXE% install --config.cafile="%CA_FILE%"
            ) else (
                %PNPM_EXE% install
            )
        )

        start "Blog Vite" cmd /k "%PNPM_EXE% run dev"
    ) else (
        if not "%NPM_EXE%"=="" (
            if not exist "node_modules" %NPM_EXE% install
            start "Blog Vite" cmd /k "%NPM_EXE% run dev"
        )
    )
)

powershell -NoProfile -Command "try { $c = New-Object Net.Sockets.TcpClient('%HOST%', %PORT%); $c.Close(); exit 0 } catch { exit 1 }" >nul 2>nul
if not errorlevel 1 (
    set "SERVER_RUNNING=1"
)

if "%SERVER_RUNNING%"=="0" (
    start "Blog Laravel" cmd /k ""%PHP_EXE%" artisan serve --host=%HOST% --port=%PORT%"
)

timeout /t 3 /nobreak >nul
start "" "%APP_URL%"

endlocal
