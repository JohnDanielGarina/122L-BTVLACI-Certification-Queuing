<?php
// Router for PHP built-in server
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// If requesting a real file, serve it directly
if ($path !== '/' && file_exists(__DIR__ . $path)) {
    return false;
}

// Default to index.php
$_SERVER['SCRIPT_NAME'] = '/index.php';
require __DIR__ . '/index.php';
