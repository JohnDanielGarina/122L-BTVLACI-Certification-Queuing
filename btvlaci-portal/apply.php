<?php 
require_once 'config.php'; 
require_once 'functions.php'; // <-- ADD THIS

auth_check('applicant');

$user_id = $_SESSION['user_id'];
$pdo = new PDO('sqlite:' . DB_PATH);

// Check if active app
$stmt = $pdo->prepare("SELECT COUNT(*) FROM applications WHERE applicant_id = ? AND status IN ('Pending','Incomplete')");
$stmt->execute([$user_id]);
if ($stmt->fetchColumn() > 0) {
  header('Location: dashboard.php');
  exit;
}

$error = $success = '';
if ($_POST && $_POST['csrf'] === $_SESSION['csrf_token']) {
  $qualification = $_POST['qualification'];
  $nc2_proof = $_POST['nc2_proof'] ?? '';
  
  if ($qualification === 'NCIII' && empty($nc2_proof)) {
    $error = 'NC II proof required for NC III.';
  } else {
    // Generate code BTV-YYYY-XXXX
    $year = date('Y');
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM applications WHERE application_code LIKE ?');
    $stmt->execute(["BTV-{$year}-%"]);
    $seq = str_pad($stmt->fetchColumn() + 1, 4, '0', STR_PAD_LEFT);
    $code = "BTV-{$year}-{$seq}";
    
    $deadline = date('Y-m-d H:i:s', time() + DOC_DEADLINE_HOURS * 3600);
    $status = 'Incomplete'; // Starts incomplete until docs
    
    $stmt = $pdo->prepare('INSERT INTO applications (applicant_id, qualification, status, application_code, doc_deadline) VALUES (?, ?, ?, ?, ?)');
    if ($stmt->execute([$user_id, $qualification, $status, $code, $deadline])) {
      $success = "Application created! ID: {$code}. Upload documents within 48 hours.";
      send_email($_SESSION['user_email'], 'Application Submitted', "Your application {$code} has been created. Complete documents soon.");
    } else {
      $error = 'Error creating application.';
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Apply - BTVLACI Portal</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
  <div class="container">
    <nav class="nav">
      <a href="dashboard.php">← Dashboard</a>
      <a href="logout.php" class="logout">Logout</a>
    </nav>
    <div class="card" style="max-width: 600px; margin: 0 auto;">
      <h2>📋 Certification Application</h2>
      <?php if ($error): ?><div class="message error"><?= $error ?></div><?php endif; ?>
      <?php if ($success): ?><div class="message success"><?= $success ?></div><?php endif; ?>
      <form method="POST" data-validate>
        <div class="form-group">
          <label>Qualification *</label>
          <select name="qualification" id="qualification" required>
            <option value="">Select...</option>
            <option value="NCII">NC II</option>
            <option value="NCIII">NC III (requires NC II proof)</option>
          </select>
        </div>
        <div class="form-group nc2-group" id="nc2_group" style="display:none;">
          <label>NC II Certificate Proof (for NC III) *</label>
          <textarea name="nc2_proof" rows="3" placeholder="Describe or note your NC II certification..."></textarea>
        </div>
        <input type="hidden" name="csrf" value="<?= $_SESSION['csrf_token'] ?>">
        <button type="submit" class="btn" style="width:100%;">Submit Application</button>
      </form>
    </div>
  </div>
  <script src="assets/script.js"></script>
</body>
</html>

