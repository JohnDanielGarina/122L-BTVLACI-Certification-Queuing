<?php
require_once 'config.php';
$pdo = new PDO('sqlite:' . DB_PATH);

// Show all users in DB
echo "<h2>All users in database:</h2>";
$users = $pdo->query("SELECT id, name, email, role, password_hash FROM applicants")->fetchAll();
foreach ($users as $u) {
    echo "<pre>";
    echo "ID: " . $u['id'] . "\n";
    echo "Name: " . $u['name'] . "\n";
    echo "Email: " . $u['email'] . "\n";
    echo "Role: " . $u['role'] . "\n";
    echo "Hash: " . $u['password_hash'] . "\n";
    echo "password_verify('admin123', hash): " . (password_verify('admin123', $u['password_hash']) ? 'YES ✅' : 'NO ❌') . "\n";
    echo "</pre><hr>";
}

// Force delete and re-insert admin
$pdo->exec("DELETE FROM applicants WHERE email = 'admin@btvlaci-portal.local'");
$hash = password_hash('admin123', PASSWORD_DEFAULT);
$stmt = $pdo->prepare("INSERT INTO applicants (email, password_hash, role, name, contact_number) VALUES (?, ?, 'admin', 'Admin User', '09000000000')");
$stmt->execute(['admin@btvlaci-portal.local', $hash]);

echo "<h2>Admin re-created!</h2>";
echo "<p>Email: admin@btvlaci-portal.local</p>";
echo "<p>Password: admin123</p>";
echo "<p>Hash: $hash</p>";
echo "<p>Verify test: " . (password_verify('admin123', $hash) ? 'PASS ✅' : 'FAIL ❌') . "</p>";
echo "<br><a href='login.php'>Go to Login</a>";
?>
