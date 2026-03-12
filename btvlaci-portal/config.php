<?php
// BTVLACI Portal Configuration
// Database
define('DB_PATH', __DIR__ . '/btvlaci.db');

// Uploads
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_TYPES', ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf']);

// Sessions & Security
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
session_start();

// CSRF
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Email (Gmail SMTP - replace with your App Password)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'admin@btvlaci-portal.local');
define('SMTP_PASS', 'your_gmail_app_password_here'); // Generate from Google Account > Security > App Passwords

// App Settings
define('APP_URL', 'http://localhost:8000');
define('DOC_DEADLINE_HOURS', 48);

// PHPMailer require will be in functions
?>

