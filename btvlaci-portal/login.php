<?php 
require_once 'config.php'; 
$role = '';

if ($_POST && $_POST['csrf'] === $_SESSION['csrf_token']) {
  $email = trim($_POST['email']);
  $password = $_POST['password'];
  
  $pdo = new PDO('sqlite:' . DB_PATH);
  $stmt = $pdo->prepare('SELECT * FROM applicants WHERE email = ?');
  $stmt->execute([$email]);
  $user = $stmt->fetch();
  
  if ($user && password_verify($password, $user['password_hash'])) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['user_name'] = $user['name'];
    $role = $user['role'];
    // Redirect by role
    header('Location: ' . ($role === 'admin' ? 'admin/admin_dashboard.php' : 'dashboard.php'));
    exit;
  } else {
    $error = 'Invalid email or password.';
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - BTVLACI Portal</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
  <div class="container">
    <div class="nav">
      <a href="index.php">← Home</a>
    </div>
    <div class="card" style="max-width: 400px; margin: 0 auto;">
      <h2>🔐 Login</h2>
      <?php if (isset($error)): ?><div class="message error"><?= $error ?></div><?php endif; ?>
      <form method="POST" data-validate>
        <div class="form-group">
          <label>Email *</label>
          <input type="email" name="email" required autofocus>
        </div>
        <div class="form-group">
          <label>Password *</label>
          <input type="password" name="password" required>
        </div>
        <input type="hidden" name="csrf" value="<?= $_SESSION['csrf_token'] ?>">
        <button type="submit" class="btn" style="width:100%;">Login</button>
      </form>
      <p style="text-align:center; margin-top:1rem;">
        <a href="register.php">New user? Register</a> | 
        Admin: admin@btvlaci-portal.local / admin123
      </p>
    </div>
  </div>
  <script src="assets/script.js"></script>
</body>
</html>

