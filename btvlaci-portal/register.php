<?php 
require_once 'config.php'; 
$error = ''; $success = '';

if ($_POST) {
  $name = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';
  $confirm = $_POST['confirm_password'] ?? '';
  $contact = trim($_POST['contact'] ?? '');

  if (!preg_match('/^[a-zA-Z\s]+$/', $name)) { $error = 'Name must contain only letters and spaces.'; }
  elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $error = 'Invalid email.'; }
  elseif (strlen($password) < 8) { $error = 'Password must be 8+ characters.'; }
  elseif ($password !== $confirm) { $error = 'Passwords do not match.'; }
  else {
    try {
      $pdo = new PDO('sqlite:' . DB_PATH, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
      $hash = password_hash($password, PASSWORD_DEFAULT);
      $stmt = $pdo->prepare('INSERT INTO applicants (name, email, password_hash, contact_number) VALUES (?, ?, ?, ?)');
      $stmt->execute([$name, $email, $hash, $contact]);
      $success = 'Registration successful! <a href="login.php">Login here</a>.';
    } catch (PDOException $e) {
      $error = 'Email already registered.';
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register - BTVLACI Portal</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
  <div class="container">
    <div class="nav">
      <a href="index.php">← Home</a>
    </div>
    <div class="card" style="max-width: 500px; margin: 0 auto;">
      <h2>📝 Register New Account</h2>
      <?php if ($error): ?><div class="message error"><?= $error ?></div><?php endif; ?>
      <?php if ($success): ?><div class="message success"><?= $success ?></div><?php endif; ?>
      <form method="POST" data-validate>
        <div class="form-group">
          <label>Full Name *</label>
          <input type="text" name="name" required maxlength="100">
        </div>
        <div class="form-group">
          <label>Email Address *</label>
          <input type="email" name="email" required>
        </div>
        <div class="form-group">
          <label>Contact Number</label>
          <input type="tel" name="contact" pattern="[0-9+ -()]{10,20}">
        </div>
        <div class="form-group">
          <label>Password *</label>
          <input type="password" name="password" required minlength="8">
        </div>
        <div class="form-group">
          <label>Confirm Password *</label>
          <input type="password" name="confirm_password" required minlength="8">
        </div>
        <input type="hidden" name="csrf" value="<?= $_SESSION['csrf_token'] ?>">
        <button type="submit" class="btn" style="width:100%;">Register</button>
      </form>
      <p style="text-align:center; margin-top:1rem;"><a href="login.php">Already registered? Login</a></p>
    </div>
  </div>
  <script src="assets/script.js"></script>
</body>
</html>

