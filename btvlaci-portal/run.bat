@echo off
cd /d "%~dp0"

echo ==========================================
echo   BTVLACI Certification Queue Portal
echo ==========================================
echo.

if not exist btvlaci.db (
    echo [INIT] Creating fresh database...
    php\php.exe -c php\php.ini init_db.php
) else (
    echo [DB] Database found.
)

echo.
echo [SERVER] Starting at http://localhost:8000
echo [ADMIN]  Hidden link at bottom of page (\"All rights reserved\")
echo [LOGIN]  admin@btvlaci-portal.local / admin123
echo.
echo Press Ctrl+C to stop the server.
echo.

php\php.exe -c php\php.ini -d extension_dir="php\ext" -S localhost:8000 router.php