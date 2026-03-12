<?php
require_once '../config.php';
require_once '../functions.php';
auth_check();

$pdo = new PDO('sqlite:' . DB_PATH);
$pending = $pdo->query("SELECT COUNT(*) FROM queues WHERE status='Pending'")->fetchColumn();
$approved = $pdo->query("SELECT COUNT(*) FROM queues WHERE status='Approved'")->fetchColumn();
$scheduled = $pdo->query("SELECT COUNT(*) FROM queues WHERE status='Scheduled'")->fetchColumn();
$total = $pdo->query("SELECT COUNT(*) FROM queues")->fetchColumn();

// Recent activity
$recent = $pdo->query("SELECT * FROM queues ORDER BY created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - BTVLACI</title>
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/admin.css">
</head>
<body>
  <div class="admin-layout">
    <?php include 'partials/sidebar.php'; ?>
    <main class="admin-main">
      <div class="admin-header">
        <div>
          <h1>Dashboard</h1>
          <p>Welcome back, <?= htmlspecialchars($_SESSION['admin_name']) ?>!</p>
        </div>
        <div class="header-date"><?= date('F j, Y') ?></div>
      </div>

      <div class="stats-grid">
        <div class="stat-card stat-pending">
          <div class="stat-icon">⏳</div>
          <div class="stat-info">
            <h2><?= $pending ?></h2>
            <p>Pending</p>
          </div>
        </div>
        <div class="stat-card stat-approved">
          <div class="stat-icon">✅</div>
          <div class="stat-info">
            <h2><?= $approved ?></h2>
            <p>Approved</p>
          </div>
        </div>
        <div class="stat-card stat-scheduled">
          <div class="stat-icon">📅</div>
          <div class="stat-info">
            <h2><?= $scheduled ?></h2>
            <p>Scheduled</p>
          </div>
        </div>
        <div class="stat-card stat-total">
          <div class="stat-icon">📋</div>
          <div class="stat-info">
            <h2><?= $total ?></h2>
            <p>Total</p>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-head">
          <h3>Recent Queue Entries</h3>
          <a href="admin_queues.php" class="btn-link">View All →</a>
        </div>
        <table>
          <thead>
            <tr><th>Queue #</th><th>Name</th><th>Certificate</th><th>Status</th><th>Date</th></tr>
          </thead>
          <tbody>
            <?php foreach ($recent as $q): ?>
            <tr>
              <td><span class="code"><?= htmlspecialchars($q['queue_code']) ?></span></td>
              <td><?= htmlspecialchars($q['name']) ?></td>
              <td><?= htmlspecialchars($q['certificate']) ?></td>
              <td><span class="badge badge-<?= strtolower($q['status']) ?>"><?= $q['status'] ?></span></td>
              <td><?= date('M j, Y', strtotime($q['created_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </main>
  </div>
</body>
</html>
