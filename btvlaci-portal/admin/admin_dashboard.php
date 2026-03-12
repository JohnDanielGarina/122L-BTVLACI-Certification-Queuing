<?php 
require_once '../config.php'; 
require_once '../functions.php'; 

auth_check('admin');

$pdo = new PDO('sqlite:' . DB_PATH);

$pending_count = $pdo->query('SELECT COUNT(*) FROM applications WHERE status = \"Pending\"')->fetchColumn();
$incomplete_count = $pdo->query('SELECT COUNT(*) FROM applications WHERE status = \"Incomplete\"')->fetchColumn();
$upcoming_batches = $pdo->query('SELECT COUNT(*) FROM batches WHERE status = \"Open\" OR status = \"Scheduled\"')->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - BTVLACI</title>
  <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
  <div class="container">
    <nav class="nav">
      <div>
        <strong>Admin Panel</strong>
      </div>
      <div>
        <a href="../dashboard.php">Applicant View</a>
        <a href="logout.php" class="logout">Logout</a>
      </div>
    </nav>
    
    <div class="grid">
      <div class="card">
        <h3>📋 Pending Applications</h3>
        <h2><?= $pending_count ?></h2>
        <a href="admin_applications.php?status=Pending" class="btn">View</a>
      </div>
      <div class="card">
        <h3>⚠️ Incomplete</h3>
        <h2><?= $incomplete_count ?></h2>
        <a href="admin_applications.php?status=Incomplete" class="btn">View</a>
      </div>
      <div class="card">
        <h3>📦 Upcoming Batches</h3>
        <h2><?= $upcoming_batches ?></h2>
        <a href="admin_batches.php" class="btn">Manage</a>
      </div>
    </div>
    
    <div class="flex gap-2 mt-4">
      <a href="admin_applications.php" class="btn">All Applications</a>
      <a href="admin_users.php" class="btn btn-secondary">Users</a>
      <a href="admin_export.php" class="btn btn-secondary">Export CSV</a>
    </div>
  </div>
</body>
</html>

