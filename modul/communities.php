<?php
session_start();
include '../lib/koneksi.php';
// Pastikan path login.php sudah benar (relatif dari modul/)
if (!isset($_SESSION['username'])) header("Location: ../login.php");

$current_user = strtolower(trim($_SESSION['username']));

// Ambil daftar semua komunitas yang ada
$q_communities = $pdo->prepare("SELECT * FROM community ORDER BY CreatedAt DESC");
$q_communities->execute();

// Ambil daftar komunitas yang sudah di-join oleh user saat ini
$q_user_joins = $pdo->prepare("
    SELECT CommunityID 
    FROM community_member 
    WHERE UserName = ?
");
$q_user_joins->execute([$current_user]);
$joined_communities = $q_user_joins->fetchAll(PDO::FETCH_COLUMN, 0); // Ambil ID saja
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Communities - X Clone</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="../asset/css/stlye.css" rel="stylesheet">
    <style>
        .community-card {
            border-radius: 15px;
            margin-bottom: 15px;
            transition: transform 0.2s;
        }
        .community-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,.05);
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">

            <?php include 'sidebar.php'; ?>

        
        <div class="col-md-7 p-4 border-end">
            <h3 class="fw-bold mb-4 border-bottom pb-2"><i class="fa-solid fa-users me-2"></i> Jelajahi Komunitas</h3>

            <?php if ($q_communities->rowCount() > 0): ?>
                <?php while ($community = $q_communities->fetch(PDO::FETCH_ASSOC)): 
                    $is_joined = in_array($community['CommunityID'], $joined_communities);
                ?>
                    <div class="card community-card p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-1 fw-bold"><?= htmlspecialchars($community['CommunityName']) ?></h5>
                                <p class="text-muted small mb-1"><?= htmlspecialchars($community['Description']) ?></p>
                                
                                <?php
                                // Ambil jumlah member
                                $q_member_count = $pdo->prepare("SELECT COUNT(*) FROM community_member WHERE CommunityID = ?");
                                $q_member_count->execute([$community['CommunityID']]);
                                $member_count = $q_member_count->fetchColumn();
                                ?>
                                <span class="small text-secondary"><i class="fa-solid fa-user-group me-1"></i> <?= $member_count ?> Member</span>
                            </div>
                            
                            <div>
                                <a href="action_community.php?id=<?= $community['CommunityID'] ?>&action=<?= $is_joined ? 'leave' : 'join' ?>"
                                   class="btn <?= $is_joined ? 'btn-outline-danger' : 'btn-primary' ?> btn-sm">
                                    <?= $is_joined ? 'Leave' : 'Join' ?>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="alert alert-info">Belum ada komunitas yang dibuat.</div>
            <?php endif; ?>
        </div>

        <div class="col-md-3 p-4">
            <div class="card p-3 bg-light">
                <h6 class="fw-bold">Tentang Komunitas</h6>
                <p class="small text-muted">Komunitas adalah tempat Anda dapat berbagi minat, bertukar pikiran, dan berdiskusi dengan orang lain.</p>
<a href="create_community.php" class="btn btn-primary btn-sm mt-2">
    <i class="fa-solid fa-plus me-1"></i> Buat Komunitas
</a>
            </div>
        </div>
        
    </div>
</div>
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