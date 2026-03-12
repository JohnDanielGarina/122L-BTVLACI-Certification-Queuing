<?php
require_once 'config.php';

if (isset($_SESSION['admin_id'])) {
    header('Location: admin/admin_dashboard.php');
    exit;
}

$error = '';
if ($_POST && $_POST['csrf'] === $_SESSION['csrf_token']) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $pdo = new PDO('sqlite:' . DB_PATH);
    $stmt = $pdo->prepare('SELECT * FROM admins WHERE email = ?');
    $stmt->execute([$email]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin && password_verify($password, $admin['password_hash'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_name'] = $admin['name'];
        $_SESSION['admin_email'] = $admin['email'];
        header('Location: admin/admin_dashboard.php');
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
  <title>Admin Login - BTVLACI</title>
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <style>
    :root { --gold:#F5C400; --dark:#1A1A2E; --dark2:#16213E; --mid:#2A2A4A; --text:#E8E8F0; --muted:#9090B0; }
    * { box-sizing:border-box; margin:0; padding:0; }
    body { font-family:'DM Sans',sans-serif; background:var(--dark); color:var(--text); min-height:100vh; display:flex; align-items:center; justify-content:center; }
    body::before { content:''; position:fixed; inset:0; background:radial-gradient(ellipse 60% 60% at 50% 40%, rgba(245,196,0,0.06) 0%, transparent 70%); pointer-events:none; }
    .login-wrap { width:100%; max-width:400px; padding:1.5rem; position:relative; z-index:1; }
    .back-link { display:inline-flex; align-items:center; gap:0.4rem; color:var(--muted); text-decoration:none; font-size:0.85rem; margin-bottom:2rem; transition:color 0.2s; }
    .back-link:hover { color:var(--gold); }
    .card { background:var(--dark2); border:1px solid rgba(255,255,255,0.06); border-radius:24px; padding:2.5rem; }
    .card-header { text-align:center; margin-bottom:2rem; }
    .card-header .icon { font-size:2.5rem; margin-bottom:1rem; }
    .card-header h1 { font-family:'Syne',sans-serif; font-size:1.5rem; font-weight:800; color:white; }
    .card-header p { color:var(--muted); font-size:0.85rem; margin-top:0.3rem; }
    .form-group { margin-bottom:1.25rem; }
    label { display:block; font-size:0.78rem; font-weight:600; color:var(--muted); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:0.5rem; }
    input { width:100%; padding:0.875rem 1rem; background:var(--mid); border:1.5px solid rgba(255,255,255,0.08); border-radius:12px; color:var(--text); font-family:'DM Sans',sans-serif; font-size:0.95rem; transition:all 0.2s; }
    input:focus { outline:none; border-color:var(--gold); background:rgba(245,196,0,0.05); }
    .btn { width:100%; padding:1rem; background:var(--gold); color:var(--dark); border:none; border-radius:14px; font-family:'Syne',sans-serif; font-size:1rem; font-weight:700; cursor:pointer; transition:all 0.2s; margin-top:0.5rem; }
    .btn:hover { background:#D4A800; transform:translateY(-2px); box-shadow:0 8px 24px rgba(245,196,0,0.25); }
    .alert { padding:0.875rem 1rem; border-radius:10px; margin-bottom:1.25rem; font-size:0.875rem; background:rgba(255,71,87,0.12); border:1px solid rgba(255,71,87,0.3); color:#FF6B7A; }
  </style>
</head>
<body>
<div class="login-wrap">
  <a href="index.php" class="back-link">← Back to Portal</a>
  <div class="card">
    <div class="card-header">
      <div class="icon">🔐</div>
      <h1>Admin Access</h1>
      <p>BTVLACI Staff Only</p>
    </div>
    <?php if ($error): ?>
      <div class="alert"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST">
      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" required autofocus>
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" required>
      </div>
      <input type="hidden" name="csrf" value="<?= $_SESSION['csrf_token'] ?>">
      <button type="submit" class="btn">Sign In →</button>
    </form>
  </div>
</div>
</body>
</html>
