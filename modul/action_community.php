<?php
session_start();
include '../lib/koneksi.php'; 

// 1. Cek Autentikasi
if (!isset($_SESSION['username'])) {
  header("Location: ../login.php");
  exit;
}

$current_user = strtolower(trim($_SESSION['username']));

// Ambil data dari POST
$community_id = intval($_POST['community_id'] ?? 0); 
$action = strtolower(trim($_POST['action'] ?? '')); // 'join' atau 'leave'

// Validasi input
if ($community_id <= 0 || !in_array($action, ['join', 'leave'])) {
  header("Location: communities.php?status=invalid_request");
  exit;
}

try {
  if ($action === 'join') {
    // 2. Cek apakah pengguna sudah menjadi anggota
    $q_check = $pdo->prepare("
      SELECT COUNT(*) 
      FROM community_member 
      WHERE CommunityID = ? AND UserName = ?
    ");
    $q_check->execute([$community_id, $current_user]);
    $is_member = $q_check->fetchColumn() > 0;

    if (!$is_member) {
      // 3. Join (Insert)
      $q_join = $pdo->prepare("
        INSERT INTO community_member (CommunityID, UserName) 
        VALUES (?, ?)
      ");
      $q_join->execute([$community_id, $current_user]);
      $status_msg = "joined";
    } else {
      $status_msg = "already_member";
    }

  } elseif ($action === 'leave') {
    // 3. Leave (Delete)
    $q_leave = $pdo->prepare("
      DELETE FROM community_member 
      WHERE CommunityID = ? AND UserName = ?
    ");
    $q_leave->execute([$community_id, $current_user]);
    $status_msg = "left";
  }

  // Redirect kembali ke halaman komunitas setelah aksi
  header("Location: communities.php?status={$status_msg}");
  exit;

} catch (PDOException $e) {
  // error_log("Error community action: " . $e->getMessage()); 
  header("Location: communities.php?status=error_action");
  exit;
}
?>