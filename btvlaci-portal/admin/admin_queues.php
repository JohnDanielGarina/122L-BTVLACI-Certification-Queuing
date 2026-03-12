<?php
require_once '../config.php';
require_once '../functions.php';
auth_check();

$pdo = new PDO('sqlite:' . DB_PATH);
$status_filter = $_GET['status'] ?? '';

// Handle actions
if ($_POST && $_POST['csrf'] === $_SESSION['csrf_token']) {
    $id = (int)$_POST['queue_id'];
    $action = $_POST['action'];

    // Get queue entry
    $stmt = $pdo->prepare("SELECT * FROM queues WHERE id = ?");
    $stmt->execute([$id]);
    $q = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($q) {
        switch ($action) {
            case 'approve':
                $schedule = $_POST['schedule_date'] ?? null;
                $notes = trim($_POST['notes'] ?? '');
                $new_status = $schedule ? 'Scheduled' : 'Approved';
                $stmt = $pdo->prepare("UPDATE queues SET status=?, schedule_date=?, notes=? WHERE id=?");
                $stmt->execute([$new_status, $schedule ?: null, $notes, $id]);
                log_activity($pdo, $_SESSION['admin_id'], 'approve', $id, "Approved {$q['queue_code']}");
                send_notification($q['email'], $q['name'], $q['queue_code'], $q['certificate'], 'approved', $schedule, $notes);
                break;
            case 'reject':
                $notes = trim($_POST['notes'] ?? '');
                $stmt = $pdo->prepare("UPDATE queues SET status='Rejected', notes=? WHERE id=?");
                $stmt->execute([$notes, $id]);
                log_activity($pdo, $_SESSION['admin_id'], 'reject', $id, "Rejected {$q['queue_code']}");
                send_notification($q['email'], $q['name'], $q['queue_code'], $q['certificate'], 'rejected', null, $notes);
                break;
        }
    }
    header('Location: ' . $_SERVER['PHP_SELF'] . ($status_filter ? '?status=' . urlencode($status_filter) : ''));
    exit;
}

// Fetch queues
$where = $status_filter ? "WHERE status = :status" : "";
$stmt = $pdo->prepare("SELECT * FROM queues $where ORDER BY created_at DESC");
if ($status_filter) $stmt->bindValue(':status', $status_filter);
$stmt->execute();
$queues = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Queue Management - BTVLACI</title>
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/admin.css">
  <style>
    .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.7); z-index:100; align-items:center; justify-content:center; }
    .modal-overlay.open { display:flex; }
    .modal { background:#16213E; border:1px solid rgba(255,255,255,0.08); border-radius:20px; padding:2rem; width:100%; max-width:440px; }
    .modal h3 { font-family:'Syne',sans-serif; font-weight:700; margin-bottom:1.25rem; color:white; }
    .modal label { display:block; font-size:0.78rem; font-weight:600; color:#9090B0; text-transform:uppercase; letter-spacing:0.06em; margin-bottom:0.4rem; margin-top:1rem; }
    .modal input, .modal textarea { width:100%; padding:0.75rem 1rem; background:#2A2A4A; border:1.5px solid rgba(255,255,255,0.08); border-radius:10px; color:#E8E8F0; font-family:'DM Sans',sans-serif; font-size:0.9rem; }
    .modal textarea { resize:vertical; min-height:80px; }
    .modal input:focus, .modal textarea:focus { outline:none; border-color:#F5C400; }
    .modal-actions { display:flex; gap:0.75rem; margin-top:1.5rem; }
    .modal-actions button { flex:1; padding:0.875rem; border-radius:12px; font-family:'Syne',sans-serif; font-weight:700; font-size:0.9rem; cursor:pointer; border:none; transition:all 0.2s; }
    .btn-confirm-approve { background:#F5C400; color:#1A1A2E; }
    .btn-confirm-approve:hover { background:#D4A800; }
    .btn-confirm-reject { background:rgba(255,71,87,0.15); color:#FF4757; border:1px solid rgba(255,71,87,0.3) !important; }
    .btn-cancel { background:#2A2A4A; color:#9090B0; }
  </style>
</head>
<body>
  <div class="admin-layout">
    <?php include 'partials/sidebar.php'; ?>
    <main class="admin-main">
      <div class="admin-header">
        <div>
          <h1>Queue Management</h1>
          <p><?= count($queues) ?> entries <?= $status_filter ? "· Filtered: {$status_filter}" : '' ?></p>
        </div>
        <a href="admin_export.php" class="btn-gold">Export CSV</a>
      </div>

      <div class="filter-tabs">
        <a href="?" class="filter-tab <?= !$status_filter ? 'active' : '' ?>">All</a>
        <a href="?status=Pending" class="filter-tab <?= $status_filter==='Pending' ? 'active' : '' ?>">Pending</a>
        <a href="?status=Approved" class="filter-tab <?= $status_filter==='Approved' ? 'active' : '' ?>">Approved</a>
        <a href="?status=Scheduled" class="filter-tab <?= $status_filter==='Scheduled' ? 'active' : '' ?>">Scheduled</a>
        <a href="?status=Rejected" class="filter-tab <?= $status_filter==='Rejected' ? 'active' : '' ?>">Rejected</a>
      </div>

      <div class="card">
        <table>
          <thead>
            <tr><th>Queue #</th><th>Name</th><th>Email</th><th>Certificate</th><th>Status</th><th>Date</th><th>Actions</th></tr>
          </thead>
          <tbody>
            <?php foreach ($queues as $q): ?>
            <tr>
              <td><span class="code"><?= htmlspecialchars($q['queue_code']) ?></span></td>
              <td><?= htmlspecialchars($q['name']) ?></td>
              <td style="color:#9090B0; font-size:0.85rem;"><?= htmlspecialchars($q['email']) ?></td>
              <td><?= htmlspecialchars($q['certificate']) ?></td>
              <td><span class="badge badge-<?= strtolower($q['status']) ?>"><?= $q['status'] ?></span></td>
              <td style="color:#9090B0; font-size:0.85rem;"><?= date('M j, Y', strtotime($q['created_at'])) ?></td>
              <td>
                <?php if ($q['status'] === 'Pending'): ?>
                  <button class="btn-sm btn-approve" onclick="openApprove(<?= $q['id'] ?>, '<?= htmlspecialchars($q['name']) ?>', '<?= htmlspecialchars($q['queue_code']) ?>')">Approve</button>
                  <button class="btn-sm btn-reject" onclick="openReject(<?= $q['id'] ?>, '<?= htmlspecialchars($q['name']) ?>')">Reject</button>
                <?php else: ?>
                  <span style="color:#9090B0; font-size:0.8rem;"><?= $q['schedule_date'] ? date('M j', strtotime($q['schedule_date'])) : '—' ?></span>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </main>
  </div>

  <!-- Approve Modal -->
  <div class="modal-overlay" id="modal-approve">
    <div class="modal">
      <h3>✅ Approve Queue Entry</h3>
      <p id="approve-name" style="color:#9090B0; font-size:0.9rem;"></p>
      <form method="POST">
        <input type="hidden" name="csrf" value="<?= $_SESSION['csrf_token'] ?>">
        <input type="hidden" name="action" value="approve">
        <input type="hidden" name="queue_id" id="approve-id">
        <label>Schedule Date & Time (optional)</label>
        <input type="datetime-local" name="schedule_date">
        <label>Notes (optional)</label>
        <textarea name="notes" placeholder="Any instructions for the applicant..."></textarea>
        <div class="modal-actions">
          <button type="button" class="btn-cancel" onclick="closeModals()">Cancel</button>
          <button type="submit" class="btn-confirm-approve">Confirm Approve</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Reject Modal -->
  <div class="modal-overlay" id="modal-reject">
    <div class="modal">
      <h3>❌ Reject Queue Entry</h3>
      <p id="reject-name" style="color:#9090B0; font-size:0.9rem;"></p>
      <form method="POST">
        <input type="hidden" name="csrf" value="<?= $_SESSION['csrf_token'] ?>">
        <input type="hidden" name="action" value="reject">
        <input type="hidden" name="queue_id" id="reject-id">
        <label>Reason for Rejection</label>
        <textarea name="notes" placeholder="Explain why this is being rejected..." required></textarea>
        <div class="modal-actions">
          <button type="button" class="btn-cancel" onclick="closeModals()">Cancel</button>
          <button type="submit" class="btn-confirm-reject">Confirm Reject</button>
        </div>
      </form>
    </div>
  </div>

  <script>
  function openApprove(id, name, code) {
    document.getElementById('approve-id').value = id;
    document.getElementById('approve-name').textContent = name + ' · ' + code;
    document.getElementById('modal-approve').classList.add('open');
  }
  function openReject(id, name) {
    document.getElementById('reject-id').value = id;
    document.getElementById('reject-name').textContent = name;
    document.getElementById('modal-reject').classList.add('open');
  }
  function closeModals() {
    document.querySelectorAll('.modal-overlay').forEach(m => m.classList.remove('open'));
  }
  </script>
</body>
</html>
