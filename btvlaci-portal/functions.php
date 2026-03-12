<?php
// Shared Functions for BTVLACI Portal
require_once 'config.php';

// Auth check
function auth_check($required_role = null) {
  if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
  }
  if ($required_role && $_SESSION['user_role'] !== $required_role) {
    header('Location: dashboard.php');
    exit;
  }
}

// Logout
function logout() {
  session_destroy();
  header('Location: index.php');
  exit;
}

// Generate app code
function generate_app_code($pdo) {
  $year = date('Y');
  $stmt = $pdo->prepare('SELECT COUNT(*) FROM applications WHERE application_code LIKE ?');
  $stmt->execute(["BTV-{$year}-%"]);
  $seq = str_pad((int)$stmt->fetchColumn() + 1, 4, '0', STR_PAD_LEFT);
  return "BTV-{$year}-{$seq}";
}

// Log admin activity
function log_activity($pdo, $admin_id, $action_type, $target_type, $target_id, $description) {
  $stmt = $pdo->prepare('INSERT INTO activity_log (admin_id, action_type, target_type, target_id, description) VALUES (?, ?, ?, ?, ?)');
  $stmt->execute([$admin_id, $action_type, $target_type, $target_id, $description]);
}

// Send email notification
function send_email($to, $subject, $body) {
  $headers = "From: admin@btvlaci-portal.local\r\n";
  $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
  $message = "<html><body><h2>BTVLACI Portal</h2><p>{$body}</p></body></html>";
  mail($to, $subject, $message, $headers);
}

// Update app status with log
function update_app_status($pdo, $app_id, $status, $admin_id = null, $reason = '') {
  $old_status = $pdo->query("SELECT status FROM applications WHERE id = {$app_id}")->fetchColumn();
  $pdo->prepare('UPDATE applications SET status = ? WHERE id = ?')->execute([$status, $app_id]);
  if ($admin_id) {
    log_activity($pdo, $admin_id, 'status_change', 'application', $app_id, "Changed from {$old_status} to {$status}: {$reason}");
  }
}
?>

