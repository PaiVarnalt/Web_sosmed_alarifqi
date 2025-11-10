<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<pre>DEBUG START\n";

session_start();
include '../lib/koneksi.php';
echo "✅ Koneksi dan session OK\n";

if (!isset($_SESSION['username'])) {
    echo "🚫 Belum login, redirect...\n";
    header("Location: login.php");
    exit;
}

$user = $_SESSION['username'];
$postID = $_POST['postID'] ?? null;
$comment = trim($_POST['comment'] ?? '');

echo "📌 Data diterima: postID=$postID, comment='$comment'\n";

if (!$postID || $comment == '') {
    echo "❌ Data kosong, stop.\n";
    exit;
}

$userData = $pdo->prepare("SELECT UserName FROM user WHERE UserName = ?");
$userData->execute([$user]);
$userID = $userData->fetchColumn();

echo "👤 User ditemukan: $userID\n";

try {
    $stmt = $pdo->prepare("INSERT INTO comment (PostID, UserName, comment) VALUES (?, ?, ?)");
    $stmt->execute([$postID, $userID, $comment]);
    echo "✅ Berhasil insert ke DB\n";
} catch (PDOException $e) {
    echo "💥 SQL Error: " . $e->getMessage() . "\n";
    exit;
}

echo "➡️ Redirect ke index.php\n";
header("Location: ../index.php");
exit;
?>
