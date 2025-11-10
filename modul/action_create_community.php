<?php
session_start();
include '../lib/koneksi.php'; 

if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit;
}

$current_user = strtolower(trim($_SESSION['username']));
$community_name = trim($_POST['community_name'] ?? '');
$description = trim($_POST['description'] ?? '');

if (empty($community_name) || empty($description)) {
    header("Location: create_community.php");
    exit;
}

try {
    // 1. INSERT Komunitas Baru ke Tabel 'community'
    $q_insert = $pdo->prepare("
        INSERT INTO community (CommunityName, Description, CreatorUser) 
        VALUES (?, ?, ?)
    ");
    $q_insert->execute([$community_name, $description, $current_user]);
    
    // Ambil ID komunitas yang baru dibuat
    $new_community_id = $pdo->lastInsertId();

    // 2. DAFTARKAN PEMBUAT sebagai anggota pertama ke Tabel 'community_member'
    $q_member = $pdo->prepare("
        INSERT INTO community_member (CommunityID, UserName) 
        VALUES (?, ?)
    ");
    $q_member->execute([$new_community_id, $current_user]);

    // Redirect sukses
    header("Location: create_community.php?status=success");
    exit;

} catch (PDOException $e) {
    // 1062 adalah kode error MySQL untuk 'Duplicate entry' (CommunityName UNIQUE)
    if ($e->getCode() === '23000' || strpos($e->getMessage(), '1062') !== false) {
        // Nama komunitas sudah ada
        header("Location: create_community.php?status=exists");
        exit;
    }
    
    // Tangani error lain
    // error_log("Error creating community: " . $e->getMessage()); 
    header("Location: create_community.php?status=error");
    exit;
}
?>