@echo off
cd /d "%~dp0btvlaci-portal"

echo ==========================================
echo   BTVLACI Certification Queue Portal
echo ==========================================
echo.

if not exist btvlaci.db (
    echo [INIT] Creating fresh database...
    php -r "
        $pdo = new PDO('sqlite:btvlaci.db');
        $pdo->exec(file_get_contents('init.sql'));
        $hash = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare(\"INSERT INTO admins (name, email, password_hash) VALUES ('Admin User', 'admin@btvlaci-portal.local', ?)\");
        $stmt->execute([$hash]);
        echo '[INIT] Database created!' . PHP_EOL;
        echo '[INIT] Admin: admin@btvlaci-portal.local / admin123' . PHP_EOL;
    "
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

php -S localhost:8000
