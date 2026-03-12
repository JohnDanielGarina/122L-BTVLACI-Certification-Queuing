<?php
// BTVLACI Queue System Configuration

// Database
define('DB_PATH', __DIR__ . '/btvlaci.db');

// Sessions & Security
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
session_start();

// CSRF
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Email (Gmail SMTP)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'admin@btvlaci-portal.local');
define('SMTP_PASS', 'your_gmail_app_password_here');

// App Settings
define('APP_URL', 'http://localhost:8000');
define('APP_NAME', 'BTVLACI Certification Portal');
?>
