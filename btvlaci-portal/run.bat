@echo off
echo Setting up BTVLACI Portal...

REM Create project structure if missing
if not exist "uploads" mkdir uploads
if not exist "lib" mkdir lib
if not exist "assets" mkdir assets
if not exist "admin" mkdir admin

REM Create DB if missing
if not exist "btvlaci.db" (
    echo Creating database...
    REM Try sqlite3 CLI (fallback to PHP)
    sqlite3 btvlaci.db < init.sql 2>nul || php -r "
\$pdo = new PDO('sqlite:btvlaci.db');
\$pdo->exec(file_get_contents('init.sql'));
echo 'DB initialized via PHP.\n';
"
)

REM Start server and open browser
echo Server starting at http://localhost:8000
start http://localhost:8000
php -S localhost:8000
