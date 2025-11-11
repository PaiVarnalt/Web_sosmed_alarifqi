<?php
session_start();
include '../lib/koneksi.php';

// Cek autentikasi dan path login
if (!isset($_SESSION['username'])) header("Location: login.php");

// >>> STANDARISASI CASE Wajib (Lower-case)
$current_user = strtolower(trim($_SESSION['username']));
$search_user = strtolower(trim($_GET['q'] ?? '')); 

// 1. Query Percakapan Aktif (Tetap sama)
$q_conversations = $pdo->prepare("
    SELECT DISTINCT 
        CASE 
            WHEN SenderUser = ? THEN ReceiverUser 
            ELSE SenderUser 
        END AS PartnerUser
    FROM messages
    WHERE SenderUser = ? OR ReceiverUser = ?
    ORDER BY SentAt DESC
");
$q_conversations->execute([$current_user, $current_user, $current_user]);

// 2. Query Pengguna Saling Follow (MUTUAL FOLLOW DIKEMBALIKAN)
$q_mutual_follows = $pdo->prepare("
    SELECT f1.FollowName AS MutualUser 
    FROM follow f1
    -- Kondisi JOIN: KITA follow PARTNER (f1) AND PARTNER follow KITA (f2)
    JOIN follow f2 
        ON f1.UserName = f2.FollowName 
        AND f1.FollowName = f2.UserName
    WHERE f1.UserName = ?
    " . ($search_user ? " AND f1.FollowName LIKE ?" : "") . "
    LIMIT 10
");

// Eksekusi Query
if ($search_user) {
    $q_mutual_follows->execute([$current_user, "%$search_user%"]);
} else {
    $q_mutual_follows->execute([$current_user]);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Messages - X Clone</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="../asset/css/stlye.css" rel="stylesheet">
    <style>
        /* Gaya tambahan yang sudah diperbaiki */
        .sidebar-custom {
            min-height: 100vh;
            padding-top: 20px;
        }
        .main-content {
            padding-top: 15px;
        }
        .messages-list-container {
            max-height: calc(100vh - 200px); 
            overflow-y: auto;
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">

            <?php include 'sidebar.php'; ?>

        
        <div class="col-md-3 p-0 border-end main-content">
            <h5 class="p-3 fw-bold border-bottom">Percakapan Aktif</h5>
            <div class="list-group list-group-flush">
            <?php 
            if ($q_conversations->rowCount() > 0) {
                while ($conv = $q_conversations->fetch(PDO::FETCH_ASSOC)) {
                    $partner = $conv['PartnerUser'];
                    $q_user_detail = $pdo->prepare("SELECT UserName FROM user WHERE UserName = ?");
                    $q_user_detail->execute([$partner]);
                    $user_detail = $q_user_detail->fetch(PDO::FETCH_ASSOC);

                    // Hitung pesan belum dibaca
                    $q_unread = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE SenderUser = ? AND ReceiverUser = ? AND IsRead = FALSE");
                    $q_unread->execute([$partner, $current_user]);
                    $unread_count = $q_unread->fetchColumn();

                    echo "<a href='chat.php?u=" . urlencode($partner) . "' class='list-group-item list-group-item-action d-flex justify-content-between align-items-center'>";
                    echo "  <strong>@" . htmlspecialchars($user_detail['UserName'] ?? $partner) . "</strong>"; 
                    
                    if ($unread_count > 0) {
                        echo "<span class='badge bg-danger rounded-pill'>{$unread_count}</span>";
                    }
                    echo "</a>";
                }
            } else {
                echo "<p class='p-3 '>Anda belum memulai chat.</p>";
            }
            ?>
            </div>
        </div>

        <div class="col-md-4 p-5 text-center  border-end main-content">
            <i class="fa-solid fa-envelope fa-3x mb-3"></i>
            <h4>Pilih percakapan</h4>
            <p>Pilih dari daftar di kiri atau cari pengguna di kanan untuk mulai *chatting*.</p>
        </div>
        
        <div class="col-md-3 p-3 main-content">
            <h5 class="fw-bold mb-3">Mulai Chat Baru</h5>
            
            <form method="get" class="mb-3">
                <div class="input-group">
                    <input type="text" name="q" class="form-control" placeholder="Cari teman chat..." value="<?= htmlspecialchars($search_user) ?>">
                    <button class="btn btn-dark" type="submit"><i class="fa fa-search"></i></button>
                </div>
            </form>

            <div class="messages-list-container">
                <h6 class="mt-3 mb-2 ">Hanya Saling Follow yang bisa chat:</h6>
                <div class="list-group list-group-flush">
                <?php if ($q_mutual_follows->rowCount() > 0): ?>
                    <?php while ($user = $q_mutual_follows->fetch(PDO::FETCH_ASSOC)): 
                        $partner = $user['MutualUser'];
                    ?>
                        <a href='chat.php?u=<?= urlencode($partner) ?>' class='list-group-item list-group-item-action'>
                           <i class="fa-regular fa-message me-2 text-success"></i> @<?= htmlspecialchars($partner) ?>
                        </a>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-muted small">
                         <?php if ($search_user): ?>
                            Tidak ada pengguna bernama "<?= htmlspecialchars($search_user) ?>" yang saling mengikuti Anda.
                        <?php else: ?>
                            Saat ini, tidak ada pengguna yang saling mengikuti (Mutual Follow) dengan Anda.
                        <?php endif; ?>
                    </p>
                <?php endif; ?>
                </div>
            </div>
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