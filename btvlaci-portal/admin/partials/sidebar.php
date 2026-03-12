<nav class="sidebar">
  <div class="sidebar-logo">
    <span class="sidebar-icon">🏗️</span>
    <div>
      <strong>BTVLACI</strong>
      <small>Admin Panel</small>
    </div>
  </div>
  <ul class="sidebar-nav">
    <li><a href="admin_dashboard.php" class="<?= basename($_SERVER['PHP_SELF']) === 'admin_dashboard.php' ? 'active' : '' ?>">
      <span>📊</span> Dashboard
    </a></li>
    <li><a href="admin_queues.php" class="<?= basename($_SERVER['PHP_SELF']) === 'admin_queues.php' ? 'active' : '' ?>">
      <span>📋</span> Queue Management
    </a></li>
    <li><a href="admin_export.php" class="<?= basename($_SERVER['PHP_SELF']) === 'admin_export.php' ? 'active' : '' ?>">
      <span>📥</span> Export CSV
    </a></li>
  </ul>
  <div class="sidebar-footer">
    <span><?= htmlspecialchars($_SESSION['admin_name'] ?? '') ?></span>
    <a href="logout.php" class="sidebar-logout">Logout</a>
  </div>
</nav>
