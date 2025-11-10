<?php
session_start();
// Path ke koneksi.php dari dalam folder modul/
include '../lib/koneksi.php'; 

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$current_user = $_SESSION['username'];
$target_user = $_GET['user'] ?? null; // Pengguna yang akan di-follow/unfollow

// Pastikan target user tidak kosong dan bukan diri sendiri
if (empty($target_user) || $target_user == $current_user) {
    header("Location: ../index.php"); // Redirect ke home jika tidak valid
    exit;
}

// 1. Cek Status Follow
$check = $pdo->prepare("SELECT COUNT(*) FROM follow WHERE UserName = ? AND FollowName = ?");
$check->execute([$current_user, $target_user]);
$is_following = $check->fetchColumn() > 0;

try {
    if ($is_following) {
        // Aksi UNFOLLOW (Hapus baris dari tabel follow)
        $q = $pdo->prepare("DELETE FROM follow WHERE UserName = ? AND FollowName = ?");
        $q->execute([$current_user, $target_user]);
        // echo "Unfollowed @{$target_user}"; // Untuk debugging
    } else {
        // Aksi FOLLOW (Masukkan baris baru ke tabel follow)
        $q = $pdo->prepare("INSERT INTO follow (UserName, FollowName) VALUES (?, ?)");
        $q->execute([$current_user, $target_user]);
        // echo "Followed @{$target_user}"; // Untuk debugging

        // >>> TAMBAH NOTIFIKASI FOLLOW BARU
        $notif = $pdo->prepare("INSERT INTO notification (TargetUser, SourceUser, Type) VALUES (?, ?, 'follow')");
        // TargetUser: Yang diikuti ($target_user). SourceUser: Yang mengikuti ($current_user).
        $notif->execute([$target_user, $current_user]);
    }
} catch (PDOException $e) {
    // Tangani error database jika terjadi (misalnya integrity constraint)
    // error_log("Follow/Unfollow error: " . $e->getMessage()); 
    // Anda bisa menambahkan pesan error di sini
}

// Redirect kembali ke halaman profil yang baru saja dilihat (atau halaman home)
header("Location: " . $_SERVER['HTTP_REFERER'] ?? "profile.php?u=" . urlencode($target_user));
exit;
?>