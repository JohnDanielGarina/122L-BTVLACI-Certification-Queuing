@echo off
php -m | findstr sqlite >nul
if errorlevel 1 (
    echo SQLite PDO driver missing. Enabling...
    echo extension=pdo_sqlite>php.ini
    echo extension=sqlite3>>php.ini
)

php -S localhost:8000 -t .
