<?php
session_start();
include '../lib/koneksi.php';

// Cek login
if (!isset($_SESSION['username'])) {
  header("Location: ../modul/login.php");
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = trim($_POST['content']);
    $username = $_SESSION['username'];
    $date = date('Y-m-d');
    $time = date('H:i:s');
    $imageId = null;

    // === Upload gambar (jika ada) ===
    if (!empty($_FILES['image']['name'])) {
        $uploadDir = realpath(__DIR__ . '/../asset/img/post/');

        // Pastikan folder ada
        if (!$uploadDir) {
            die("❌ Folder upload tidak ditemukan!");
        }

        // Pastikan bisa ditulis
        if (!is_writable($uploadDir)) {
            die("❌ Folder tidak bisa ditulis: $uploadDir<br>Silakan ubah permission folder jadi 777 (Windows: Full Control).");
        }

        $imageName = time() . '_' . basename($_FILES['image']['name']);
        $targetPath = $uploadDir . DIRECTORY_SEPARATOR . $imageName;

        // Debug untuk lihat lokasi file tmp
        // echo "<pre>"; print_r($_FILES['image']); echo "</pre>"; exit;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            // Simpan data gambar ke tabel image
            $stmt = $pdo->prepare("INSERT INTO image (ImageName, Date) VALUES (?, ?)");
            $stmt->execute([$imageName, $date]);
            $imageId = $pdo->lastInsertId();
        } else {
            die("❌ Gagal upload gambar ke: " . htmlspecialchars($targetPath));
        }
    }

    // === Simpan postingan ke tabel post ===
    $stmt = $pdo->prepare("INSERT INTO post (Date, Time, Text, UserName) VALUES (?, ?, ?, ?)");
    $stmt->execute([$date, $time, $content, $username]);
    $postId = $pdo->lastInsertId();

    // === Simpan relasi ke detailpost (jika ada gambar) ===
    if ($imageId) {
        $stmt = $pdo->prepare("INSERT INTO detailpost (PostID, ImageID) VALUES (?, ?)");
        $stmt->execute([$postId, $imageId]);
    }

    header("Location: ../index.php");
    exit;
}
?>
