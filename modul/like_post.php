<?php
session_start();
include '../lib/koneksi.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$current_username = $_SESSION['username']; 
$postID = $_POST['postID'] ?? null;

// Hentikan proses jika PostID kosong
if (!$postID) {
    // Redirect ke home jika postID tidak valid
    header("Location: ../index.php"); 
    exit;
}

// Cek apakah user sudah like
$cek = $pdo->prepare("SELECT * FROM likes WHERE postID=? AND UserName=?");
$cek->execute([$postID, $current_username]);

if ($cek->rowCount() > 0) {
    // 1. Jika sudah, maka unlike (DELETE)
    $pdo->prepare("DELETE FROM likes WHERE postID=? AND UserName=?")->execute([$postID, $current_username]);
    
} else {
    // 2. Jika belum, maka like (INSERT)
    $pdo->prepare("INSERT INTO likes (postID, UserName) VALUES (?, ?)")->execute([$postID, $current_username]);

    // =======================================================
    // >>> LOGIKA NOTIFIKASI BARU UNTUK LIKE <<<
    // =======================================================
    
    // a. Dapatkan pemilik postingan (TargetUser)
    $post_owner_q = $pdo->prepare("SELECT UserName FROM post WHERE PostID = ?");
    $post_owner_q->execute([$postID]);
    $post_owner = $post_owner_q->fetchColumn();

    // b. Pastikan user tidak me-like postingannya sendiri sebelum membuat notifikasi
    if ($post_owner != $current_username) {
        
        // c. TAMBAH NOTIFIKASI LIKE BARU
        $notif = $pdo->prepare("
            INSERT INTO notification (TargetUser, SourceUser, PostID, Type) 
            VALUES (?, ?, ?, 'like')
        ");
        
        // TargetUser: Pemilik post ($post_owner)
        // SourceUser: Yang me-like ($current_username)
        $notif->execute([$post_owner, $current_username, $postID]);
    }
}

// Redirect kembali ke halaman sebelumnya (lebih fleksibel)
header("Location: " . $_SERVER['HTTP_REFERER'] ?? '../index.php');
exit;
?>