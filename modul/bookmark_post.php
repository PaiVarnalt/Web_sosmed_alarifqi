<?php
session_start();
// Pastikan path ke koneksi.php sudah benar dari dalam folder modul/
include '../lib/koneksi.php'; 

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$current_username = $_SESSION['username']; 
$postID = $_POST['postID'] ?? null;

// Hentikan proses jika PostID kosong
if (!$postID) {
    header("Location: ../index.php"); 
    exit;
}

// Cek apakah user sudah bookmark
$cek = $pdo->prepare("SELECT * FROM bookmarks WHERE PostID=? AND UserName=?");
$cek->execute([$postID, $current_username]);

if ($cek->rowCount() > 0) {
    // 1. Jika sudah, maka unbookmark (DELETE)
    $pdo->prepare("DELETE FROM bookmarks WHERE PostID=? AND UserName=?")->execute([$postID, $current_username]);
    
    // TIDAK ADA NOTIFIKASI JIKA UNBOOKMARK

} else {
    // 2. Jika belum, maka bookmark (INSERT)
    $pdo->prepare("INSERT INTO bookmarks (PostID, UserName) VALUES (?, ?)")->execute([$postID, $current_username]);

    // =======================================================
    // >>> LOGIKA NOTIFIKASI BARU UNTUK BOOKMARK <<<
    // =======================================================
    
    // a. Dapatkan pemilik postingan (TargetUser)
    $post_owner_q = $pdo->prepare("SELECT UserName FROM post WHERE PostID = ?");
    $post_owner_q->execute([$postID]);
    $post_owner = $post_owner_q->fetchColumn();

    // b. Pastikan user tidak mem-bookmark postingannya sendiri sebelum membuat notifikasi
    // dan pastikan TargetUser ada
    if ($post_owner && $post_owner != $current_username) {
        
        // c. TAMBAH NOTIFIKASI BOOKMARK BARU
        // CATATAN PENTING: Notifikasi bookmark ini hanya untuk memberi tahu pemilik post
        // bahwa postingan mereka DISIMPAN oleh orang lain.
        $notif = $pdo->prepare("
            INSERT INTO notification (TargetUser, SourceUser, PostID, Type) 
            VALUES (?, ?, ?, 'bookmark')
        ");
        
        // TargetUser: Pemilik post ($post_owner)
        // SourceUser: Yang mem-bookmark ($current_username)
        $notif->execute([$post_owner, $current_username, $postID]);
    }
}

// Redirect kembali ke halaman sebelumnya
header("Location: " . $_SERVER['HTTP_REFERER'] ?? '../index.php');
exit;
?>