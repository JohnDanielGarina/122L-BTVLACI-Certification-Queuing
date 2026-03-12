<?php 
require_once 'config.php'; 
require_once 'functions.php'; // We'll create this later

auth_check('applicant');

$pdo = new PDO('sqlite:' . DB_PATH);
$user_id = $_SESSION['user_id'];

// Check active application
$stmt = $pdo->prepare('SELECT * FROM applications WHERE applicant_id = ? AND status IN (\"Pending\",\"Incomplete\",\"Approved\",\"Rejected\",\"Scheduled\") ORDER BY created_at DESC LIMIT 1');
$stmt->execute([$user_id]);
$app = $stmt->fetch();

$show_apply = !$app;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard - BTVLACI Portal</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
  <div class="container">
    <nav class="nav">
      <div>
        <strong>Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?>!</strong>
      </div>
      <div>
        <a href="documents.php<?= $app ? '?app_id=' . $app['id'] : '' ?>">Documents</a>
        <a href="logout.php" class="logout">Logout</a>
      </div>
    </nav>
    
    <?php if ($show_apply): ?>
      <div class="card">
        <h2>🚀 Start Your Application</h2>
        <p>No active application found. Click below to begin.</p>
        <a href="apply.php" class="btn">Apply for Certification</a>
        <button class="btn btn-secondary refresh-status">Refresh Status</button>
      </div>
    <?php else: ?>
      <div class="card">
        <h2>📊 Application Status: <span class="status-badge status-<?= strtolower($app['status']) ?>"><?= ucfirst($app['status']) ?></span></h2>
        <div class="grid">
          <div>
            <strong>Application ID:</strong> <?= htmlspecialchars($app['application_code']) ?>
          </div>
          <div>
            <strong>Qualification:</strong> <?= $app['qualification'] === 'NCII' ? 'NC II' : 'NC III' ?>
          </div>
          <?php if ($app['batch_id']): 
            $batch = $pdo->prepare('SELECT * FROM batches WHERE id = ?')->execute([$app['batch_id']]);
            $batch = $pdo->fetch();
          ?>
          <div>
            <strong>Batch:</strong> <?= htmlspecialchars($batch['batch_code'] ?? '') ?> | <?= $batch['schedule_datetime'] ?? '' ?>
          </div>
          <?php endif; ?>
        </div>
        <button class="btn btn-secondary refresh-status" onclick="location.reload()">Refresh</button>
      </div>
    <?php endif; ?>
  </div>
  <script src="assets/script.js"></script>
</body>
</html>

