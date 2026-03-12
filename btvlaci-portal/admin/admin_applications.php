<?php 
require_once '../config.php'; 
require_once '../functions.php'; 
auth_check('admin');

$pdo = new PDO('sqlite:' . DB_PATH);
$status_filter = $_GET['status'] ?? '';
$where = $status_filter ? 'WHERE status = :status' : '';
$stmt = $pdo->prepare("SELECT a.*, ap.name, ap.email FROM applications a JOIN applicants ap ON a.applicant_id = ap.id $where ORDER BY a.created_at DESC");
if ($status_filter) $stmt->bindValue(':status', $status_filter);
$stmt->execute();
$apps = $stmt->fetchAll();

if ($_POST && $_POST['csrf'] === $_SESSION['csrf_token']) {
  $app_id = $_POST['app_id'];
  $action = $_POST['action'];
  $reason = $_POST['reason'] ?? '';
  $batch_id = $_POST['batch_id'] ?? null;
  
  switch ($action) {
    case 'approve':
      update_app_status($pdo, $app_id, 'Approved');
      // TODO: assign batch logic
      break;
    case 'reject':
      update_app_status($pdo, $app_id, 'Rejected', $_SESSION['user_id'], $reason);
      break;
    case 'assign_batch':
      $pdo->prepare('UPDATE applications SET batch_id = ?, status = \"Scheduled\" WHERE id = ?')->execute([$batch_id, $app_id]);
      log_activity($pdo, $_SESSION['user_id'], 'batch_assign', 'application', $app_id, "Assigned to batch {$batch_id}");
      break;
  }
  header('Location: ' . $_SERVER['PHP_SELF'] . ($status_filter ? '?status=' . $status_filter : ''));
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Applications - Admin</title>
  <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
  <div class="container">
    <nav class="nav">
      <a href="admin_dashboard.php">← Dashboard</a>
      <a href="../logout.php" class="logout">Logout</a>
    </nav>
    <div class="card">
      <h2>📋 All Applications <?= $status_filter ? ' - ' . ucfirst($status_filter) : '' ?></h2>
      <div class="flex gap-2 mb-4">
        <a href="?status=Pending" class="btn btn-secondary">Pending</a>
        <a href="?status=Incomplete" class="btn btn-secondary">Incomplete</a>
        <a href="?" class="btn">All</a>
      </div>
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Applicant</th>
            <th>Qual</th>
            <th>Status</th>
            <th>Created</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($apps as $app): ?>
          <tr>
            <td><?= htmlspecialchars($app['application_code']) ?></td>
            <td><?= htmlspecialchars($app['name']) ?> (<?= $app['email'] ?>)</td>
            <td><?= $app['qualification'] ?></td>
            <td><span class="status-badge status-<?= strtolower($app['status']) ?>"><?= $app['status'] ?></span></td>
            <td><?= date('M j', strtotime($app['created_at'])) ?></td>
            <td>
              <form method="POST" style="display:inline;">
                <input type="hidden" name="csrf" value="<?= $_SESSION['csrf_token'] ?>">
                <input type="hidden" name="app_id" value="<?= $app['id'] ?>">
                <button type="submit" name="action" value="approve" class="btn btn-secondary" style="padding:0.5rem;">Approve</button>
                <button type="submit" name="action" value="reject" class="btn btn-danger" style="padding:0.5rem;" onclick="return confirm('Reject?')">Reject</button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>
</html>

