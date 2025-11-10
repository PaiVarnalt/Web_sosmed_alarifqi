<?php
session_start();
include '../lib/koneksi.php'; 

if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit;
}

// >>> STANDARISASI CASE Wajib
$sender = strtolower(trim($_SESSION['username']));
$receiver = strtolower(trim($_POST['receiver'] ?? null));
$message_text = trim($_POST['message_text'] ?? '');

if (empty($receiver) || empty($message_text) || $sender == $receiver) {
    header("Location: messages.php");
    exit;
}

// 🛑 VALIDASI MUTUAL FOLLOW KEMBALI DI SINI
$q_check_mutual = $pdo->prepare("
    SELECT COUNT(*) 
    FROM follow f1
    JOIN follow f2 ON f1.UserName = f2.FollowName AND f1.FollowName = f2.UserName
    WHERE f1.UserName = ? AND f1.FollowName = ?
");
$q_check_mutual->execute([$sender, $receiver]);
$is_allowed_to_chat = $q_check_mutual->fetchColumn() > 0;

if (!$is_allowed_to_chat) {
    // Jika tidak saling follow, tidak ada pesan yang terkirim. Redirect.
    header("Location: messages.php?error=not_authorized");
    exit;
}
// 🛑 AKHIR VALIDASI MUTUAL FOLLOW

try {
    // INSERT pesan ke database
    $q = $pdo->prepare("
        INSERT INTO messages (SenderUser, ReceiverUser, MessageText) 
        VALUES (?, ?, ?)
    ");
    $q->execute([$sender, $receiver, $message_text]);

} catch (PDOException $e) {
    // error_log("Error sending message: " . $e->getMessage()); 
}

// Redirect kembali ke ruang obrolan
header("Location: chat.php?u=" . urlencode($receiver));
exit;
?>