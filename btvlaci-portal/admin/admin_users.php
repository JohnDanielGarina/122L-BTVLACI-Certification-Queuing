<?php 
require_once '../config.php'; 
require_once '../functions.php'; 
auth_check('admin');

$pdo = new PDO('sqlite:' . DB_PATH);
$users = $pdo->query('SELECT * FROM applicants WHERE role = \'applicant\' ORDER BY created_at DESC')->fetchAll();

if ($_POST && $_POST['csrf'] === $_SESSION['csrf_token']) {
  $user_id = $_POST['user_id'];
  $action = $_POST['action']; // reset_password, deactivate, reactivate
  // Logic here
  log_activity($pdo, $_SESSION['user_id'], $action, 'user', $user_id, "Performed {$action}");
  header('Location: ' . $_SERVER['PHP_SELF']);
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Users - Admin</title>
  <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
  <div class="container">
    <nav class="nav">
      <a href="admin_dashboard.php">← Dashboard</a>
      <a href="../logout.php" class="logout">Logout</a>
    </nav>
    <div class="card">
      <h2>👥 Applicant Management</h2>
      <table>
        <thead>
          <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Contact</th>
            <th>Created</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($users as $user): ?>
          <tr>
            <td><?= htmlspecialchars($user['name']) ?></td>
            <td><?= htmlspecialchars($user['email']) ?></td>
            <td><?= htmlspecialchars($user['contact_number']) ?></td>
            <td><?= date('M j, Y', strtotime($user['created_at'])) ?></td>
            <td>
              <form method="POST" style="display:inline;">
                <input type="hidden" name="csrf" value="<?= $_SESSION['csrf_token'] ?>">
                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                <button type="submit" name="action" value="reset_password" class="btn btn-secondary" style="padding:0.375rem 0.75rem; font-size:0.875rem;">Reset PW</button>
                <button type="submit" name="action" value="deactivate" class="btn btn-danger" style="padding:0.375rem 0.75rem; font-size:0.875rem;">Deactivate</button>
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

