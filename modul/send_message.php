<?php
session_start();
include '../lib/koneksi.php'; 

if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit;
}

$sender = $_SESSION['username'];
$receiver = $_POST['receiver'] ?? null;
$message_text = trim($_POST['message_text'] ?? '');

if (empty($receiver) || empty($message_text) || $sender == $receiver) {
    header("Location: messages.php");
    exit;
}

try {
    // 1. CEK STATUS SALING FOLLOW (MUTUAL FOLLOW)
    $q_check_follow = $pdo->prepare("
        SELECT 
            (SELECT COUNT(*) FROM follow WHERE UserName = ? AND FollowName = ?) AS is_following_receiver,
            (SELECT COUNT(*) FROM follow WHERE UserName = ? AND FollowName = ?) AS is_followed_by_receiver
    ");
    $q_check_follow->execute([$sender, $receiver, $receiver, $sender]);
    $follow_status = $q_check_follow->fetch(PDO::FETCH_ASSOC);

    $is_mutual_follow = ($follow_status['is_following_receiver'] > 0 && $follow_status['is_followed_by_receiver'] > 0);

    // 2. Jika saling follow, lanjutkan pengiriman pesan
    if ($is_mutual_follow) {
        // Simpan pesan ke database
        $q = $pdo->prepare("
            INSERT INTO messages (SenderUser, ReceiverUser, MessageText) 
            VALUES (?, ?, ?)
        ");
        $q->execute([$sender, $receiver, $message_text]);
    } else {
        // Redirect dengan error jika tidak saling follow
        header("Location: chat.php?u=" . urlencode($receiver) . "&error=not_mutual");
        exit;
    }

} catch (PDOException $e) {
    // Handle error
}

// Redirect kembali ke ruang obrolan
header("Location: chat.php?u=" . urlencode($receiver));
exit;
?>