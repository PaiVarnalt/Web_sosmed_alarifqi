<?php
// Pastikan sesi sudah dimulai
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// === START: LOGIKA PENGHITUNGAN NOTIFIKASI BARU ===
$notif_count = 0;
if (isset($_SESSION['username'])) {
    $current_user = $_SESSION['username'];
    
    // Logika cerdas untuk menentukan path koneksi dari mana pun sidebar.php di-include
    $koneksi_path = 'lib/koneksi.php'; 
    if (!file_exists($koneksi_path)) {
        $koneksi_path = '../lib/koneksi.php'; 
    }
    
    // Hanya hitung jika file koneksi ditemukan
    if (file_exists($koneksi_path)) {
        // Hati-hati: Asumsikan variabel $pdo sudah ada setelah include koneksi.php
        include $koneksi_path;
        
        // Ambil jumlah notifikasi yang belum dibaca (IsRead = FALSE)
        try {
            $q_count = $pdo->prepare("
                SELECT COUNT(*) 
                FROM notification 
                WHERE TargetUser = ? AND IsRead = FALSE
            ");
            $q_count->execute([$current_user]);
            $notif_count = $q_count->fetchColumn();
        } catch (PDOException $e) {
            // Jika tabel 'notification' belum ada atau query error
            $notif_count = 0; 
        }
    }
}
// === END: LOGIKA PENGHITUNGAN NOTIFIKASI BARU ===
?>

<div class="col-md-3 col-lg-2 sidebar border-end">
  <div>
    <div class="px-3 mb-4">
      <i class="fa-brands fa-x-twitter fa-2x"></i>
      <p class="mt-2 fw-bold">@<?= htmlspecialchars($_SESSION['username']) ?></p>
    </div>

    <nav class="nav flex-column">
      <a href="index.php" class="nav-link"><i class="fa-solid fa-house"></i> <span>Home</span></a>
      <a href="modul/explore.php" class="nav-link"><i class="fa-solid fa-magnifying-glass"></i> <span>Explore</span></a>
      
      <a href="modul/notifications.php" class="nav-link d-flex justify-content-between align-items-center">
          <div>
              <i class="fa-regular fa-bell"></i> <span>Notifications</span>
          </div>
          <?php if ($notif_count > 0): ?>
              <span class="badge bg-danger rounded-pill fw-bold" style="font-size: 0.75em;"><?= $notif_count ?></span>
          <?php endif; ?>
      </a>
      
      <a href="modul/messages.php" class="nav-link"><i class="fa-regular fa-envelope"></i> <span>Messages</span></a>
      <a href="modul/bookmarks.php" class="nav-link"><i class="fa-regular fa-bookmark"></i> <span>Bookmarks</span></a>
      <a href="modul/communities.php" class="nav-link"><i class="fa-solid fa-user-group"></i> <span>Communities</span></a>
      <a href="modul/profile.php" class="nav-link"><i class="fa-regular fa-user"></i> <span>Profile</span></a>
      <a href="modul/setting.php" class="nav-link"><i class="fa-solid fa-gear"></i> <span>Settings</span></a>
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