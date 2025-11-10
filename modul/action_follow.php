<?php
session_start();
// Path ke koneksi.php dari dalam folder modul/
include '../lib/koneksi.php'; 
// WARNING: Pastikan path login.php sudah benar (jika ini dipanggil dari folder modul/)
if (!isset($_SESSION['username'])) {
    header("Location: login.php"); // Mengarahkan ke ../login.php
    exit;
}

$current_user_raw = $_SESSION['username'];
$target_user_raw = $_GET['user'] ?? null; 

// >>> STANDARISASI CASE UNTUK SQL (Penting untuk menghindari masalah case sensitivity)
$current_user = strtolower(trim($current_user_raw));
$target_user = strtolower(trim($target_user_raw));


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
        
        // Catatan: Jika Anda tidak lagi menggunakan Mutual Follow,
        // tidak perlu menghapus baris balikkan di sini.

    } else {
        // Aksi FOLLOW (Masukkan baris baru ke tabel follow)
        $q = $pdo->prepare("INSERT INTO follow (UserName, FollowName) VALUES (?, ?)");
        $q->execute([$current_user, $target_user]);

        // >>> TAMBAH NOTIFIKASI FOLLOW BARU
        $notif = $pdo->prepare("INSERT INTO notification (TargetUser, SourceUser, Type) VALUES (?, ?, 'follow')");
        // TargetUser: Yang diikuti ($target_user). SourceUser: Yang mengikuti ($current_user).
        $notif->execute([$target_user, $current_user]);
    }
} catch (PDOException $e) {
    // Tangani error database jika terjadi
    // error_log("Follow/Unfollow error: " . $e->getMessage()); 
    // Anda bisa menambahkan pesan error atau redirect ke halaman error
}

// Redirect kembali ke halaman profil yang baru saja dilihat (atau halaman home)
// Fallback jika HTTP_REFERER tidak ada
$fallback_url = "../index.php"; 
if (isset($_SERVER['HTTP_REFERER'])) {
    $fallback_url = $_SERVER['HTTP_REFERER'];
} else {
    // Jika tidak ada referer, redirect ke profil target
    $fallback_url = "profile.php?u=" . urlencode($target_user_raw); 
}

header("Location: " . $fallback_url);
exit;
?>