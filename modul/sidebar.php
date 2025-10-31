<div class="sidebar d-flex flex-column justify-content-between">
  <div>
    <h4 class="text-center mb-4">Sosmed</h4>
    <a href="index.php">🏠 Home</a>
    <a href="modul/search.php">🔍 Search</a>
    <?php if (isset($_SESSION['user_id'])): ?>
      <a href="modul/profile.php">👤 Profile</a>
      <a href="modul/logout.php" class="text-danger">🚪 Logout</a>
    <?php else: ?>
      <a href="modul/login.php">🔐 Login</a>
      <a href="modul/register.php">📝 Register</a>
    <?php endif; ?>
  </div>
  <button id="theme-toggle" class="btn btn-sm btn-light toggle-theme">🌙</button>
</div>
