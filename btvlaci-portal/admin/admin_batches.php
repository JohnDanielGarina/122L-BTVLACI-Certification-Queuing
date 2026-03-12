<?php 
require_once '../config.php'; 
require_once '../functions.php'; 
auth_check('admin');

$pdo = new PDO('sqlite:' . DB_PATH);

if ($_POST && $_POST['csrf'] === $_SESSION['csrf_token']) {
  if (isset($_POST['create_batch'])) {
    $qual = $_POST['qualification'];
    $schedule = $_POST['schedule_datetime'];
    $min = $_POST['min_size'];
    $max = $_POST['max_size'];
    $code = 'BTVB-' . date('Y') . '-' . str_pad(rand(1,9999), 4, '0', STR_PAD_LEFT);
    
    $stmt = $pdo->prepare('INSERT INTO batches (batch_code, qualification, schedule_datetime, min_size, max_size) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([$code, $qual, $schedule, $min, $max]);
    log_activity($pdo, $_SESSION['user_id'], 'batch_create', 'batch', $pdo->lastInsertId(), "Created batch {$code}");
  }
  // Add app to batch logic similar
}

$batches = $pdo->query('SELECT * FROM batches ORDER BY created_at DESC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Batches - Admin</title>
  <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
  <div class="container">
    <nav class="nav">
      <a href="admin_dashboard.php">← Dashboard</a>
      <a href="../logout.php" class="logout">Logout</a>
    </nav>
    <div class="card">
      <h2>📦 Manage Batches</h2>
      <form method="POST">
        <div class="grid">
          <div class="form-group">
            <label>Qualification</label>
            <select name="qualification" required>
              <option value="NCII">NC II</option>
              <option value="NCIII">NC III</option>
            </select>
          </div>
          <div class="form-group">
            <label>Schedule Date/Time</label>
            <input type="datetime-local" name="schedule_datetime" required>
          </div>
          <div class="form-group">
            <label>Min Size</label>
            <input type="number" name="min_size" value="10" min="1">
          </div>
          <div class="form-group">
            <label>Max Size</label>
            <input type="number" name="max_size" required min="10">
          </div>
        </div>
        <input type="hidden" name="csrf" value="<?= $_SESSION['csrf_token'] ?>">
        <button type="submit" name="create_batch" class="btn">Create Batch</button>
      </form>
      
      <h3>Current Batches</h3>
      <table>
        <thead>
          <tr>
            <th>Code</th>
            <th>Qual</th>
            <th>Schedule</th>
            <th>Size (Current/Min-Max)</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($batches as $batch): ?>
          <tr>
            <td><?= htmlspecialchars($batch['batch_code']) ?></td>
            <td><?= $batch['qualification'] ?></td>
            <td><?= $batch['schedule_datetime'] ?></td>
            <td><?= $pdo->query("SELECT COUNT(*) FROM applications WHERE batch_id = {$batch['id']}")->fetchColumn() ?>/<?= $batch['min_size'] ?>-<?= $batch['max_size'] ?></td>
            <td><?= $batch['status'] ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>
</html>

