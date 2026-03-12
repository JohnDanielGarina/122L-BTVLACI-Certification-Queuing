<?php
require_once '../config.php';
require_once '../functions.php';
auth_check();

$pdo = new PDO('sqlite:' . DB_PATH);

if (isset($_GET['download'])) {
    $status = $_GET['status'] ?? '';
    $where = $status ? "WHERE status = :status" : "";
    $stmt = $pdo->prepare("SELECT queue_code, name, email, phone, certificate, status, schedule_date, notes, created_at FROM queues $where ORDER BY created_at DESC");
    if ($status) $stmt->bindValue(':status', $status);
    $stmt->execute();

    $filename = 'btvlaci_queue_' . date('Y-m-d') . ($status ? "_{$status}" : '') . '.csv';
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Queue #', 'Name', 'Email', 'Phone', 'Certificate', 'Status', 'Schedule Date', 'Notes', 'Submitted']);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($out, $row);
    }
    exit;
}

$counts = [];
foreach (['Pending','Approved','Scheduled','Rejected'] as $s) {
    $counts[$s] = $pdo->query("SELECT COUNT(*) FROM queues WHERE status='{$s}'")->fetchColumn();
}
$total = $pdo->query("SELECT COUNT(*) FROM queues")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Export CSV - BTVLACI</title>
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/admin.css">
</head>
<body>
  <div class="admin-layout">
    <?php include 'partials/sidebar.php'; ?>
    <main class="admin-main">
      <div class="admin-header">
        <div>
          <h1>Export CSV</h1>
          <p>Download queue data for TESDA records</p>
        </div>
      </div>

      <div class="card" style="padding:2rem;">
        <h3 style="font-family:'Syne',sans-serif; font-weight:700; color:white; margin-bottom:1.5rem;">Download Options</h3>
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:1rem;">
          <a href="?download=1" class="export-btn">
            <span style="font-size:1.5rem;">📋</span>
            <strong>All Entries</strong>
            <span><?= $total ?> records</span>
          </a>
          <?php foreach ($counts as $status => $count): ?>
          <a href="?download=1&status=<?= $status ?>" class="export-btn">
            <span style="font-size:1.5rem;">
              <?= $status==='Pending' ? '⏳' : ($status==='Approved' ? '✅' : ($status==='Scheduled' ? '📅' : '❌')) ?>
            </span>
            <strong><?= $status ?></strong>
            <span><?= $count ?> records</span>
          </a>
          <?php endforeach; ?>
        </div>
      </div>
    </main>
  </div>
  <style>
    .export-btn {
      display:flex; flex-direction:column; align-items:center; gap:0.5rem;
      background:var(--mid); border:1px solid rgba(255,255,255,0.08); border-radius:16px;
      padding:1.5rem; text-decoration:none; color:var(--text); transition:all 0.2s; text-align:center;
    }
    .export-btn strong { font-family:'Syne',sans-serif; font-weight:700; color:white; }
    .export-btn span:last-child { color:var(--muted); font-size:0.8rem; }
    .export-btn:hover { border-color:var(--gold); transform:translateY(-2px); }
  </style>
</body>
</html>
