<?php
session_start();
include '../lib/koneksi.php'; 

if (!isset($_SESSION['username'])) {
  header("Location: login.php");
  exit;
}

$current_user_name = $_SESSION['username'];

// 1. Ambil Notifikasi
$q_notifs = $pdo->prepare("
    SELECT 
        n.*, 
        p.Text AS PostText 
    FROM notification n
    LEFT JOIN post p ON n.PostID = p.PostID
    WHERE n.TargetUser = ?
    ORDER BY n.CreatedAt DESC
    LIMIT 20
");
$q_notifs->execute([$current_user_name]);

// 2. Tandai semua notifikasi baru sebagai "sudah dibaca" setelah diambil
$update_read = $pdo->prepare("UPDATE notification SET IsRead = TRUE WHERE TargetUser = ? AND IsRead = FALSE");
$update_read->execute([$current_user_name]);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Notifikasi - X Clone</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link href="../asset/css/stlye.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container-fluid">
    <div class="row">
        

            <?php include 'sidebar.php'; ?>

        
        <div class="col-md-6 p-3 border-start border-end"> 
            <h4 class="fw-bold mb-4"><i class="fa-solid fa-bell"></i> Notifikasi</h4>
            
            <?php if ($q_notifs->rowCount() > 0): ?>
                <?php while ($notif = $q_notifs->fetch(PDO::FETCH_ASSOC)): ?>
                    
                    <?php 
                        $icon = '';
                        $message = '';
                        $link = '';

                        // Tentukan ikon dan pesan berdasarkan tipe notifikasi
                        switch ($notif['Type']) {
                            case 'like':
                                $icon = '<i class="fa-solid fa-heart text-danger me-2"></i>';
                                $message = " menyukai postingan Anda.";
                                $link = "detail_post.php?id={$notif['PostID']}"; // Asumsi ada halaman detail_post
                                break;
                            case 'comment':
                                $icon = '<i class="fa-solid fa-comment text-primary me-2"></i>';
                                $message = " mengomentari postingan Anda.";
                                $link = "detail_post.php?id={$notif['PostID']}";
                                break;
                            case 'follow':
                                $icon = '<i class="fa-solid fa-user-plus text-success me-2"></i>';
                                $message = " mulai mengikuti Anda.";
                                $link = "profile.php?u={$notif['SourceUser']}";
                                break;
                            default:
                                $icon = '<i class="fa-solid fa-info-circle text-muted me-2"></i>';
                                $message = " memiliki aktivitas baru.";
                                $link = "#";
                        }
                    ?>

                    <a href="<?= htmlspecialchars($link) ?>" class="text-decoration-none text-dark">
                        <div class="p-3 mb-2 border rounded bg-white shadow-sm <?= !$notif['IsRead'] ? 'border-primary' : '' ?>">
                            <small class="float-end text-muted"><?= date("H:i", strtotime($notif['CreatedAt'])) ?></small>
                            <?= $icon ?>
                            <strong>@<?= htmlspecialchars($notif['SourceUser']) ?></strong>
                            <?= $message ?>
                            
                            <?php if (!empty($notif['PostText'])): ?>
                                <blockquote class="blockquote small mt-1 mb-0 border-start ps-2">
                                    "<?= substr(htmlspecialchars($notif['PostText']), 0, 50) ?>..."
                                </blockquote>
                            <?php endif; ?>
                        </div>
                    </a>

                <?php endwhile; ?>
            <?php else: ?>
                <div class='p-5 text-center text-muted border rounded bg-white'>Tidak ada notifikasi baru.</div>
            <?php endif; ?>
        </div>

        <div class="col-md-3 p-3 border-start">
             </div>
        
    </div>
</div>

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
</body>
</html>