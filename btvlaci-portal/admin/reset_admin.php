<?php
require_once 'config.php';
$pdo = new PDO('sqlite:' . DB_PATH);

$hash = password_hash('admin123', PASSWORD_DEFAULT);

$pdo->exec("DELETE FROM applicants WHERE email = 'admin@btvlaci-portal.local'");
$stmt = $pdo->prepare("INSERT INTO applicants (email, password_hash, role, name) VALUES (?, ?, 'admin', 'Admin User')");
$stmt->execute(['admin@btvlaci-portal.local', $hash]);

echo "Admin reset! Hash: " . $hash;
?>