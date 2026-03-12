<?php 
require_once 'config.php'; 
require_once 'functions.php'; // <-- ADD THIS

auth_check('applicant');

$user_id = $_SESSION['user_id'];
$app_id = $_GET['app_id'] ?? 0;

$pdo = new PDO('sqlite:' . DB_PATH);

// Get app
$stmt = $pdo->prepare('SELECT * FROM applications WHERE id = ? AND applicant_id = ?');
$stmt->execute([$app_id, $user_id]);
$app = $stmt->fetch();
if (!$app) { header('Location: dashboard.php'); exit; }

// Check deadline & update status if expired
$deadline_passed = strtotime($app['doc_deadline']) < time();
if ($deadline_passed && $app['status'] === 'Incomplete') {
  $pdo->prepare("UPDATE applications SET status = 'Rejected' WHERE id = ?")->execute([$app_id]); // <-- fixed quotes
  $app['status'] = 'Rejected';
  $reason = 'Documents not uploaded within 48 hours.';
}

// Handle uploads
if ($_POST && $_POST['csrf'] === $_SESSION['csrf_token']) {
  foreach (['id_card', 'photo_2x2'] as $type) {
    if (isset($_FILES[$type]) && $_FILES[$type]['error'] === UPLOAD_ERR_OK) {
      $file = $_FILES[$type];
      if (in_array($file['type'], ALLOWED_TYPES) && $file['size'] <= MAX_FILE_SIZE) {
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = $type . '_' . $app_id . '_' . time() . '.' . $ext;
        $path = UPLOAD_DIR . $filename;
        if (move_uploaded_file($file['tmp_name'], $path)) {
          $stmt = $pdo->prepare('INSERT INTO documents (application_id, type, file_path, mime_type, size_bytes) VALUES (?, ?, ?, ?, ?)');
          $stmt->execute([$app_id, $type, $filename, $file['type'], $file['size']]);
        }
      }
    }
  }
  // Check if complete
  $countStmt = $pdo->prepare('SELECT COUNT(*) FROM documents WHERE application_id = ?');
  $countStmt->execute([$app_id]);
  $docs_count = $countStmt->fetchColumn();
  $required = $app['qualification'] === 'NCIII' ? 3 : 2;
  if ($docs_count >= $required) {
    $pdo->prepare("UPDATE applications SET status = 'Pending' WHERE id = ?")->execute([$app_id]);
  }
}

// Get existing docs
$stmt = $pdo->prepare('SELECT * FROM documents WHERE application_id = ?');
$stmt->execute([$app_id]);
$docs = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Documents - BTVLACI Portal</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
  <div class="container">
    <nav class="nav">
      <a href="dashboard.php">← Dashboard</a>
      <a href="logout.php" class="logout">Logout</a>
    </nav>
    <div class="card">
      <h2>📎 Document Upload</h2>
      <p><strong>App ID:</strong> <?= $app['application_code'] ?> | Deadline: <?= date('M j, Y H:i', strtotime($app['doc_deadline'])) ?></p>
      <p>Status: <span class="status-badge status-<?= strtolower($app['status']) ?>"><?= $app['status'] ?></span></p>
      
      <form method="POST" enctype="multipart/form-data" data-validate>
        <div class="grid">
          <div class="form-group">
            <label>Valid ID (PDF/JPG/PNG, max 5MB)</label>
            <input type="file" name="id_card" accept="image/*,application/pdf">
            <?php if (isset($docs[0])): ?><a href="uploads/<?= $docs[0]['file_path'] ?>" target="_blank">View Uploaded</a><?php endif; ?>
          </div>
          <div class="form-group">
            <label>2x2 Photo (PDF/JPG/PNG, max 5MB)</label>
            <input type="file" name="photo_2x2" accept="image/*,application/pdf">
            <?php if (isset($docs[1])): ?><a href="uploads/<?= $docs[1]['file_path'] ?>" target="_blank">View Uploaded</a><?php endif; ?>
          </div>
          <?php if ($app['qualification'] === 'NCIII'): ?>
          <div class="form-group">
            <label>NC II Certificate (PDF/JPG/PNG, max 5MB)</label>
            <input type="file" name="nc2_certificate" accept="image/*,application/pdf">
            <?php if (isset($docs[2])): ?><a href="uploads/<?= $docs[2]['file_path'] ?>" target="_blank">View Uploaded</a><?php endif; ?>
          </div>
          <?php endif; ?>
        </div>
        <input type="hidden" name="csrf" value="<?= $_SESSION['csrf_token'] ?>">
        <button type="submit" class="btn">Upload Documents</button>
      </form>
      
      <div style="margin-top:2rem;">
        <h3>Uploaded Files</h3>
        <?php if (empty($docs)): ?>
          <p>No documents uploaded yet.</p>
        <?php else: ?>
          <ul>
            <?php foreach ($docs as $doc): ?>
              <li><?= ucwords(str_replace('_', ' ', $doc['type'])) ?> - <a href="uploads/<?= $doc['file_path'] ?>" target="_blank"><?= $doc['file_path'] ?></a></li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <script src="assets/script.js"></script>
</body>
</html>

