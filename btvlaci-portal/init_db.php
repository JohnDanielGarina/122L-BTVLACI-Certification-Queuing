<?php
// Database initialization script - run once on first setup
echo "[INIT] Initializing database..." . PHP_EOL;

try {
    $pdo = new PDO('sqlite:' . __DIR__ . '/btvlaci.db');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Run schema
    $sql = file_get_contents(__DIR__ . '/init.sql');
    $pdo->exec($sql);

    // Create admin account
    $hash = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT OR IGNORE INTO admins (name, email, password_hash) VALUES ('Admin User', 'admin@btvlaci-portal.local', ?)");
    $stmt->execute([$hash]);

    echo "[INIT] Database created successfully!" . PHP_EOL;
    echo "[INIT] Admin: admin@btvlaci-portal.local / admin123" . PHP_EOL;
} catch (Exception $e) {
    echo "[ERROR] " . $e->getMessage() . PHP_EOL;
}
?>
