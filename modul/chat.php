<?php
session_start();
include '../lib/koneksi.php';
if (!isset($_SESSION['username'])) header("Location: ../login.php");

// >>> STANDARISASI CASE Wajib
$current_user = strtolower(trim($_SESSION['username']));
$partner_user = strtolower(trim($_GET['u'] ?? null));

if (!$partner_user || $current_user == $partner_user) {
    header("Location: messages.php");
    exit;
}

// ✅ 1. HILANGKAN VALIDASI Mutual Follow DARI SINI (membiarkan pengguna masuk)

// 2. CEK STATUS MUTUAL FOLLOW (untuk menampilkan peringatan dan mengontrol pengiriman)
$q_check_mutual = $pdo->prepare("
    SELECT COUNT(*) 
    FROM follow f1
    JOIN follow f2 ON f1.UserName = f2.FollowName AND f1.FollowName = f2.UserName
    WHERE f1.UserName = ? AND f1.FollowName = ?
");
$q_check_mutual->execute([$current_user, $partner_user]);
$is_allowed_to_send = $q_check_mutual->fetchColumn() > 0; // True jika Mutual Follow

// Ambil Riwayat Pesan
$q_messages = $pdo->prepare("
    SELECT * FROM messages
    WHERE (SenderUser = ? AND ReceiverUser = ?) 
       OR (SenderUser = ? AND ReceiverUser = ?)
    ORDER BY SentAt ASC
");
$q_messages->execute([$current_user, $partner_user, $partner_user, $current_user]);

// Tandai Pesan yang Diterima sebagai Sudah Dibaca
$pdo->prepare("UPDATE messages SET IsRead = TRUE WHERE SenderUser = ? AND ReceiverUser = ?")->execute([$partner_user, $current_user]);
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
             <a href="messages.php" class="p-3 d-block btn">← Kembali ke Daftar Chat</a>
        </div>

        <div class="col-md-6 d-flex flex-column" style="height: 100vh;">
            <h5 class="p-3 fw-bold border-bottom">Chatting dengan @<?= htmlspecialchars($partner_user) ?></h5>
            
            <div class="flex-grow-1 chat-box" id="chat-container">
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
            </div>

            <div class="p-3 border-top">
                <?php if (!$is_allowed_to_send): ?>
                    <div class="alert alert-warning small" role="alert">
                        Pesan Anda tidak akan terkirim karena Anda dan @<?= htmlspecialchars($partner_user) ?> belum saling mengikuti (Mutual Follow).
                        Anda harus saling mengikuti agar pesan bisa masuk.
                    </div>
                    <div class="d-flex">
                        <input type="text" class="form-control me-2" placeholder="Tulis pesan..." disabled>
                        <button type="button" class="btn btn-primary" disabled><i class="fa-solid fa-paper-plane"></i> Kirim</button>
                    </div>
                <?php else: ?>
                    <form action="send_message.php" method="POST" class="d-flex">
                        <input type="hidden" name="receiver" value="<?= htmlspecialchars($partner_user) ?>">
                        <input type="text" name="message_text" class="form-control me-2" placeholder="Tulis pesan..." required>
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-paper-plane"></i> Kirim</button>
                    </form>
                <?php endif; ?>
            </div>
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
<script>
const toggle = document.getElementById('darkToggle');
if(toggle){
  toggle.addEventListener('click', () => {
    document.body.classList.toggle('dark-mode');
    localStorage.setItem('theme', document.body.classList.contains('dark-mode') ? 'dark-mode' : '');
  });
}

if(localStorage.getItem('theme') === 'dark-mode'){
  document.body.classList.add('dark-mode');
}
</script>
</html>