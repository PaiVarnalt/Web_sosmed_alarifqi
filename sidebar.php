<?php
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}
?>

<div class="col-md-3 col-lg-2 sidebar border-end">
  <div>
    <div class="px-3 mb-4">
      <i class="fa-brands fa-x-twitter fa-2x"></i>
      <p class="mt-2 fw-bold">@<?= htmlspecialchars($_SESSION['username']) ?></p>
    </div>

    <nav class="nav flex-column">
      <a href="index.php"><i class="fa-solid fa-house"></i> <span>Home</span></a>
      <a href="#"><i class="fa-solid fa-magnifying-glass"></i> <span>Explore</span></a>
      <a href="#"><i class="fa-regular fa-bell"></i> <span>Notifications</span></a>
      <a href="#"><i class="fa-regular fa-envelope"></i> <span>Messages</span></a>
      <a href="#"><i class="fa-regular fa-bookmark"></i> <span>Bookmarks</span></a>
      <a href="#"><i class="fa-solid fa-user-group"></i> <span>Communities</span></a>
      <a href="modul/profile.php"><i class="fa-regular fa-user"></i> <span>Profile</span></a>
      <a href="#"><i class="fa-solid fa-gear"></i> <span>Settings</span></a>
    </nav>

    <div class="px-3 mt-4">
      <button class="post-btn"><i class="fa-solid fa-feather"></i> Post</button>
    </div>
  </div>

  <div class="px-3 mt-4">
    <button class="btn btn-outline-secondary w-100" id="darkToggle">
      <i class="fa-solid fa-moon"></i> Mode
    </button>
  </div>
</div>
