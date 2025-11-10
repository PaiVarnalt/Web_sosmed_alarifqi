<?php
session_start();
include '../lib/koneksi.php';
if (!isset($_SESSION['username'])) header("Location: ../login.php");

$current_user = $_SESSION['username'];
$partner_user = $_GET['u'] ?? null;

if (!$partner_user) {
    header("Location: messages.php");
    exit;
}

// 1. CEK STATUS SALING FOLLOW
$q_check_follow = $pdo->prepare("
    SELECT 
        (SELECT COUNT(*) FROM follow WHERE UserName = ? AND FollowName = ?) AS is_following_receiver,
        (SELECT COUNT(*) FROM follow WHERE UserName = ? AND FollowName = ?) AS is_followed_by_receiver
");
$q_check_follow->execute([$current_user, $partner_user, $partner_user, $current_user]);
$follow_status = $q_check_follow->fetch(PDO::FETCH_ASSOC);

$is_mutual_follow = ($follow_status['is_following_receiver'] > 0 && $follow_status['is_followed_by_receiver'] > 0);

$q_messages = null; 

// 2. Ambil Riwayat Pesan HANYA JIKA SALING FOLLOW
if ($is_mutual_follow) {
    $q_messages = $pdo->prepare("
        SELECT * FROM messages
        WHERE (SenderUser = ? AND ReceiverUser = ?) 
           OR (SenderUser = ? AND ReceiverUser = ?)
        ORDER BY SentAt ASC
    ");
    $q_messages->execute([$current_user, $partner_user, $partner_user, $current_user]);

    // Tandai Pesan yang Diterima sebagai Sudah Dibaca
    $pdo->prepare("UPDATE messages SET IsRead = TRUE WHERE SenderUser = ? AND ReceiverUser = ?")->execute([$partner_user, $current_user]);
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Chat with @<?= $partner_user ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="../asset/css/stlye.css" rel="stylesheet">
    <style>
        .chat-box { height: 70vh; overflow-y: scroll; padding: 15px; }
        .message { padding: 8px 12px; border-radius: 15px; margin-bottom: 10px; max-width: 70%; }
        .sent { background-color: #0d6efd; color: white; margin-left: auto; }
        .received { background-color: #e9ecef; color: black; margin-right: auto; }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <?php include 'sidebar.php'; ?>
        
        <div class="col-md-3 p-0 border-end">
             <h5 class="p-3 fw-bold border-bottom">Chats</h5>
             <p class="p-3 text-muted">Daftar percakapan...</p>
        </div>

        <div class="col-md-6 d-flex flex-column" style="height: 100vh;">
            <h5 class="p-3 fw-bold border-bottom">Chatting dengan @<?= htmlspecialchars($partner_user) ?></h5>
            
            <div class="flex-grow-1 chat-box" id="chat-container">
                <?php if ($is_mutual_follow && $q_messages): ?>
                    <?php while ($msg = $q_messages->fetch(PDO::FETCH_ASSOC)): ?>
                        <?php $is_sent = ($msg['SenderUser'] == $current_user); ?>
                        <div class="d-flex <?= $is_sent ? 'justify-content-end' : 'justify-content-start' ?>">
                            <div class="message <?= $is_sent ? 'sent' : 'received' ?>">
                                <?= htmlspecialchars($msg['MessageText']) ?>
                                <div class="small text-end" style="opacity: 0.7; font-size: 0.7em;">
                                    <?= date("H:i", strtotime($msg['SentAt'])) ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="alert alert-warning text-center mt-5" role="alert">
                        <i class="fa-solid fa-lock me-2"></i> **Pesan tidak dapat dikirim/dilihat.** Anda berdua harus saling mengikuti untuk memulai percakapan.
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($is_mutual_follow): ?>
                <div class="p-3 border-top">
                    <form action="send_message.php" method="POST" class="d-flex">
                        <input type="hidden" name="receiver" value="<?= htmlspecialchars($partner_user) ?>">
                        <input type="text" name="message_text" class="form-control me-2" placeholder="Tulis pesan..." required>
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-paper-plane"></i> Kirim</button>
                    </form>
                </div>
            <?php else: ?>
                 <div class="p-3 border-top bg-light-subtle text-muted text-center">
                    Tidak dapat mengirim pesan.
                 </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var chatContainer = document.getElementById('chat-container');
    if(chatContainer) {
        chatContainer.scrollTop = chatContainer.scrollHeight;
    }
});
</script>
</body>
</html>