<?php require_once 'config.php'; 

$error = $success = $queue_info = '';
$queue_number = '';

// Handle queue form submission
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'queue') {
  if ($_POST['csrf'] !== $_SESSION['csrf_token']) { $error = 'Invalid request.'; }
  else {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $certificate = $_POST['certificate'] ?? '';

    if (!$name || !$email || !$certificate) {
      $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $error = 'Please enter a valid email address.';
    } else {
      $pdo = new PDO('sqlite:' . DB_PATH, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
      
      // Generate queue number: BTV-YYYY-XXXX
      $year = date('Y');
      $stmt = $pdo->prepare("SELECT COUNT(*) FROM queues WHERE queue_code LIKE ?");
      $stmt->execute(["BTV-{$year}-%"]);
      $seq = str_pad((int)$stmt->fetchColumn() + 1, 4, '0', STR_PAD_LEFT);
      $code = "BTV-{$year}-{$seq}";

      $stmt = $pdo->prepare("INSERT INTO queues (name, email, phone, certificate, queue_code, status) VALUES (?, ?, ?, ?, ?, 'Pending')");
      if ($stmt->execute([$name, $email, $phone, $certificate, $code])) {
        // Send confirmation email
        send_notification($email, $name, $code, $certificate, 'queued');
        $success = $code;
      } else {
        $error = 'Something went wrong. Please try again.';
      }
    }
  }
}

// Handle status lookup
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'lookup') {
  $lookup_code = strtoupper(trim($_POST['queue_number'] ?? ''));
  if ($lookup_code) {
    $pdo = new PDO('sqlite:' . DB_PATH);
    $stmt = $pdo->prepare("SELECT * FROM queues WHERE queue_code = ?");
    $stmt->execute([$lookup_code]);
    $queue_info = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$queue_info) $error = 'Queue number not found.';
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>BTVLACI Certification Queue</title>
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <style>
    :root {
      --gold: #F5C400;
      --gold-dark: #D4A800;
      --gold-light: #FFF3B0;
      --dark: #1A1A2E;
      --dark2: #16213E;
      --mid: #2A2A4A;
      --text: #E8E8F0;
      --muted: #9090B0;
      --white: #FFFFFF;
      --success: #00C896;
      --danger: #FF4757;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'DM Sans', sans-serif;
      background: var(--dark);
      color: var(--text);
      min-height: 100vh;
      overflow-x: hidden;
    }

    /* Background pattern */
    body::before {
      content: '';
      position: fixed;
      inset: 0;
      background: 
        radial-gradient(ellipse 80% 50% at 20% 20%, rgba(245,196,0,0.08) 0%, transparent 60%),
        radial-gradient(ellipse 60% 40% at 80% 80%, rgba(245,196,0,0.05) 0%, transparent 50%);
      pointer-events: none;
      z-index: 0;
    }

    .page-wrap { position: relative; z-index: 1; }

    /* HEADER */
    header {
      padding: 2rem 2rem 1rem;
      display: flex;
      align-items: center;
      gap: 1rem;
    }
    .logo-badge {
      width: 48px; height: 48px;
      background: var(--gold);
      border-radius: 14px;
      display: flex; align-items: center; justify-content: center;
      font-size: 1.5rem;
      flex-shrink: 0;
    }
    .logo-text h1 {
      font-family: 'Syne', sans-serif;
      font-size: 1.25rem;
      font-weight: 800;
      color: var(--white);
      letter-spacing: -0.02em;
    }
    .logo-text p {
      font-size: 0.75rem;
      color: var(--muted);
      margin-top: 0.1rem;
    }

    /* HERO */
    .hero {
      text-align: center;
      padding: 3rem 2rem 2rem;
      max-width: 680px;
      margin: 0 auto;
    }
    .hero-tag {
      display: inline-block;
      background: rgba(245,196,0,0.15);
      border: 1px solid rgba(245,196,0,0.3);
      color: var(--gold);
      font-size: 0.75rem;
      font-weight: 600;
      letter-spacing: 0.1em;
      text-transform: uppercase;
      padding: 0.4rem 1rem;
      border-radius: 100px;
      margin-bottom: 1.5rem;
    }
    .hero h2 {
      font-family: 'Syne', sans-serif;
      font-size: clamp(2rem, 5vw, 3.5rem);
      font-weight: 800;
      line-height: 1.1;
      letter-spacing: -0.03em;
      color: var(--white);
      margin-bottom: 1rem;
    }
    .hero h2 span { color: var(--gold); }
    .hero p {
      color: var(--muted);
      font-size: 1rem;
      line-height: 1.7;
      max-width: 480px;
      margin: 0 auto;
    }

    /* TABS */
    .tabs-wrap {
      max-width: 680px;
      margin: 2rem auto 0;
      padding: 0 1.5rem;
    }
    .tabs {
      display: flex;
      background: var(--mid);
      border-radius: 16px;
      padding: 0.375rem;
      gap: 0.25rem;
      margin-bottom: 1.5rem;
    }
    .tab-btn {
      flex: 1;
      padding: 0.75rem;
      border: none;
      border-radius: 12px;
      background: transparent;
      color: var(--muted);
      font-family: 'DM Sans', sans-serif;
      font-size: 0.9rem;
      font-weight: 500;
      cursor: pointer;
      transition: all 0.2s;
    }
    .tab-btn.active {
      background: var(--gold);
      color: var(--dark);
      font-weight: 700;
    }

    /* PANELS */
    .panel { display: none; }
    .panel.active { display: block; }

    /* CARD */
    .card {
      background: var(--dark2);
      border: 1px solid rgba(255,255,255,0.06);
      border-radius: 24px;
      padding: 2rem;
      margin-bottom: 1.5rem;
    }

    /* FORM */
    .form-group { margin-bottom: 1.25rem; }
    label {
      display: block;
      font-size: 0.8rem;
      font-weight: 600;
      color: var(--muted);
      text-transform: uppercase;
      letter-spacing: 0.06em;
      margin-bottom: 0.5rem;
    }
    input, select {
      width: 100%;
      padding: 0.875rem 1rem;
      background: var(--mid);
      border: 1.5px solid rgba(255,255,255,0.08);
      border-radius: 12px;
      color: var(--text);
      font-family: 'DM Sans', sans-serif;
      font-size: 0.95rem;
      transition: all 0.2s;
      appearance: none;
    }
    input:focus, select:focus {
      outline: none;
      border-color: var(--gold);
      background: rgba(245,196,0,0.05);
    }
    select option { background: var(--dark2); }

    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }

    /* BUTTONS */
    .btn-primary {
      width: 100%;
      padding: 1rem;
      background: var(--gold);
      color: var(--dark);
      border: none;
      border-radius: 14px;
      font-family: 'Syne', sans-serif;
      font-size: 1rem;
      font-weight: 700;
      cursor: pointer;
      transition: all 0.2s;
      margin-top: 0.5rem;
      letter-spacing: 0.02em;
    }
    .btn-primary:hover {
      background: var(--gold-dark);
      transform: translateY(-2px);
      box-shadow: 0 8px 24px rgba(245,196,0,0.25);
    }
    .btn-secondary {
      width: 100%;
      padding: 1rem;
      background: var(--mid);
      color: var(--text);
      border: 1.5px solid rgba(255,255,255,0.1);
      border-radius: 14px;
      font-family: 'Syne', sans-serif;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s;
      margin-top: 0.5rem;
    }
    .btn-secondary:hover { border-color: var(--gold); color: var(--gold); }

    /* ALERTS */
    .alert {
      padding: 1rem 1.25rem;
      border-radius: 12px;
      margin-bottom: 1.25rem;
      font-size: 0.9rem;
      font-weight: 500;
    }
    .alert-error { background: rgba(255,71,87,0.12); border: 1px solid rgba(255,71,87,0.3); color: #FF6B7A; }
    .alert-success {
      background: rgba(0,200,150,0.1);
      border: 1px solid rgba(0,200,150,0.3);
      color: var(--success);
    }

    /* SUCCESS CARD */
    .success-card {
      text-align: center;
      padding: 2.5rem 2rem;
    }
    .success-icon {
      width: 72px; height: 72px;
      background: rgba(0,200,150,0.15);
      border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      font-size: 2rem;
      margin: 0 auto 1.5rem;
    }
    .queue-number-display {
      font-family: 'Syne', sans-serif;
      font-size: 2.5rem;
      font-weight: 800;
      color: var(--gold);
      letter-spacing: 0.05em;
      margin: 1rem 0;
      padding: 1rem;
      background: rgba(245,196,0,0.08);
      border: 2px dashed rgba(245,196,0,0.3);
      border-radius: 16px;
    }
    .success-card p { color: var(--muted); font-size: 0.9rem; line-height: 1.6; }

    /* STATUS CARD */
    .status-card { padding: 1.5rem; }
    .status-card h3 {
      font-family: 'Syne', sans-serif;
      font-weight: 700;
      margin-bottom: 1.25rem;
      color: var(--white);
    }
    .status-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 0.75rem 0;
      border-bottom: 1px solid rgba(255,255,255,0.05);
      font-size: 0.9rem;
    }
    .status-row:last-child { border-bottom: none; }
    .status-label { color: var(--muted); }
    .status-value { color: var(--white); font-weight: 500; }
    .badge {
      padding: 0.35rem 0.85rem;
      border-radius: 100px;
      font-size: 0.75rem;
      font-weight: 700;
      letter-spacing: 0.05em;
      text-transform: uppercase;
    }
    .badge-pending { background: rgba(245,196,0,0.15); color: var(--gold); }
    .badge-approved { background: rgba(0,200,150,0.15); color: var(--success); }
    .badge-rejected { background: rgba(255,71,87,0.15); color: var(--danger); }
    .badge-scheduled { background: rgba(100,149,237,0.15); color: #6495ED; }

    /* FOOTER */
    footer {
      text-align: center;
      padding: 3rem 2rem 2rem;
      color: var(--muted);
      font-size: 0.8rem;
    }
    /* Hidden admin button - looks like copyright text */
    .admin-secret {
      color: var(--muted);
      text-decoration: none;
      font-size: 0.8rem;
      cursor: default;
      user-select: none;
    }
    .admin-secret:hover { color: var(--muted); }

    @media (max-width: 600px) {
      .form-row { grid-template-columns: 1fr; }
      .hero { padding: 2rem 1.5rem 1.5rem; }
      .tabs-wrap { padding: 0 1rem; }
      .card { padding: 1.5rem; }
    }
  </style>
</head>
<body>
<div class="page-wrap">
  <header>
    <div class="logo-badge">🏗️</div>
    <div class="logo-text">
      <h1>BTVLACI Portal</h1>
      <p>Certification Queue System</p>
    </div>
  </header>

  <div class="hero">
    <div class="hero-tag">Now Open for Claims</div>
    <h2>Claim Your <span>Certificate</span> Today</h2>
    <p>Queue up online and we'll notify you once your certificate is ready for pickup. No login required.</p>
  </div>

  <div class="tabs-wrap">
    <?php if ($error): ?>
      <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
      <!-- Show success confirmation -->
      <div class="card success-card">
        <div class="success-icon">✅</div>
        <h3 style="font-family:'Syne',sans-serif; font-size:1.5rem; font-weight:800; color:var(--white); margin-bottom:0.5rem;">You're in the queue!</h3>
        <p>Your queue number is:</p>
        <div class="queue-number-display"><?= htmlspecialchars($success) ?></div>
        <p>A confirmation has been sent to your email.<br>Save your queue number to check your status later.</p>
        <button class="btn-secondary" style="margin-top:1.5rem;" onclick="location.reload()">Queue Another</button>
      </div>

    <?php elseif ($queue_info): ?>
      <!-- Show status lookup result -->
      <div class="card status-card">
        <h3>Queue Status</h3>
        <div class="status-row">
          <span class="status-label">Queue Number</span>
          <span class="status-value" style="font-family:'Syne',sans-serif; color:var(--gold); font-weight:700;"><?= htmlspecialchars($queue_info['queue_code']) ?></span>
        </div>
        <div class="status-row">
          <span class="status-label">Name</span>
          <span class="status-value"><?= htmlspecialchars($queue_info['name']) ?></span>
        </div>
        <div class="status-row">
          <span class="status-label">Certificate</span>
          <span class="status-value"><?= htmlspecialchars($queue_info['certificate']) ?></span>
        </div>
        <div class="status-row">
          <span class="status-label">Status</span>
          <span class="badge badge-<?= strtolower($queue_info['status']) ?>"><?= $queue_info['status'] ?></span>
        </div>
        <?php if ($queue_info['schedule_date']): ?>
        <div class="status-row">
          <span class="status-label">Scheduled Date</span>
          <span class="status-value"><?= date('F j, Y g:i A', strtotime($queue_info['schedule_date'])) ?></span>
        </div>
        <?php endif; ?>
        <?php if ($queue_info['notes']): ?>
        <div class="status-row">
          <span class="status-label">Notes</span>
          <span class="status-value"><?= htmlspecialchars($queue_info['notes']) ?></span>
        </div>
        <?php endif; ?>
        <button class="btn-secondary" style="margin-top:1rem;" onclick="location.reload()">← Back</button>
      </div>

    <?php else: ?>
      <!-- Tabs -->
      <div class="tabs">
        <button class="tab-btn active" onclick="switchTab('queue', this)">Join Queue</button>
        <button class="tab-btn" onclick="switchTab('status', this)">Check Status</button>
      </div>

      <!-- Queue Form -->
      <div id="panel-queue" class="panel active">
        <div class="card">
          <form method="POST">
            <input type="hidden" name="action" value="queue">
            <input type="hidden" name="csrf" value="<?= $_SESSION['csrf_token'] ?>">
            <div class="form-row">
              <div class="form-group">
                <label>Full Name *</label>
                <input type="text" name="name" placeholder="Juan dela Cruz" required>
              </div>
              <div class="form-group">
                <label>Email Address *</label>
                <input type="email" name="email" placeholder="juan@email.com" required>
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label>Phone Number</label>
                <input type="tel" name="phone" placeholder="09XX XXX XXXX">
              </div>
              <div class="form-group">
                <label>Certificate to Claim *</label>
                <select name="certificate" required>
                  <option value="">Select certificate...</option>
                  <option value="NC II">NC II</option>
                  <option value="NC III">NC III</option>
                </select>
              </div>
            </div>
            <button type="submit" class="btn-primary">Get Queue Number →</button>
          </form>
        </div>
      </div>

      <!-- Status Lookup -->
      <div id="panel-status" class="panel">
        <div class="card">
          <form method="POST">
            <input type="hidden" name="action" value="lookup">
            <div class="form-group">
              <label>Your Queue Number</label>
              <input type="text" name="queue_number" placeholder="BTV-2026-0001" style="text-transform:uppercase; letter-spacing:0.05em; font-family:'Syne',sans-serif; font-size:1.1rem;">
            </div>
            <button type="submit" class="btn-primary">Check Status →</button>
          </form>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <footer>
    <p>© 2026 BTVLACI Certification Portal &nbsp;·&nbsp; <a href="admin/admin_dashboard.php" class="admin-secret" tabindex="-1">All rights reserved</a></p>
  </footer>
</div>

<script>
function switchTab(tab, btn) {
  document.querySelectorAll('.panel').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
  document.getElementById('panel-' + tab).classList.add('active');
  btn.classList.add('active');
}
</script>
</body>
</html>
