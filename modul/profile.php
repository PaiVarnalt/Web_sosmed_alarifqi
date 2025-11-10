<?php
session_start();
include '../lib/koneksi.php'; 

if (!isset($_SESSION['username'])) {
  header("Location: login.php");
  exit;
}

$current_user = $_SESSION['username'];
$view_user = trim($_GET['u'] ?? $current_user); // Tentukan user yang dilihat

// 1. Ambil Data Profil User yang Dilihat
$u_data = $pdo->prepare("SELECT * FROM user WHERE UserName = ?");
$u_data->execute([$view_user]);
$profile = $u_data->fetch(PDO::FETCH_ASSOC);

if (!$profile) {
    die("Pengguna @{$view_user} tidak ditemukan.");
}

// Tentukan path foto profil.
// ... (Bagian 1. Ambil Data Profil User yang Dilihat)
$u_data->execute([$view_user]);
$profile = $u_data->fetch(PDO::FETCH_ASSOC);

if (!$profile) {
    die("Pengguna @{$view_user} tidak ditemukan.");
}

// Kolom FOTO PROFIL sudah ditambahkan ke tabel user.
// Tentukan path foto profil.
// Asumsi: Foto profil disimpan di '../asset/img/namafile.jpg'
if (!empty($profile['FotoProfil'])) {
    $foto_profil = '../asset/img/user/' . $profile['FotoProfil'];
} else {
    // Gunakan gambar default jika kolom FotoProfil kosong atau NULL
    $foto_profil = '../asset/img/user/default.jpg';
}

// ... (lanjut ke Bagian 2. Hitung Followers & Following)
// 2. Hitung Followers & Following
$q_followers = $pdo->prepare("SELECT COUNT(*) FROM follow WHERE FollowName = ?");
$q_followers->execute([$view_user]);
$total_followers = $q_followers->fetchColumn(); 

$q_following = $pdo->prepare("SELECT COUNT(*) FROM follow WHERE UserName = ?");
$q_following->execute([$view_user]);
$total_following = $q_following->fetchColumn(); 

// 3. Cek Status Follow
$is_following = false;
if ($current_user != $view_user) {
    $check = $pdo->prepare("SELECT COUNT(*) FROM follow WHERE UserName = ? AND FollowName = ?");
    $check->execute([$current_user, $view_user]);
    $is_following = $check->fetchColumn() > 0;
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Profil @<?= $view_user ?> - X Clone</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="../asset/css/stlye.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container-fluid">
    <div class="row">
        

            <?php include 'sidebar.php'; ?>

        
        <div class="col-md-9 p-3 border-start"> 
            
            <div class="mb-3">
                <img src="<?= htmlspecialchars($foto_profil) ?>" 
                     alt="Foto Profil <?= htmlspecialchars($view_user) ?>" 
                     width="120" height="120" class="rounded-circle border border-2 shadow-sm">
            </div>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h4 class="fw-bold mb-0">@<?= htmlspecialchars($view_user) ?></h4>
                    <h5 class="text-muted mt-0"><?= htmlspecialchars($profile['FirstName'] . ' ' . $profile['LastName']) ?></h5>
                </div>
                
                <?php if ($current_user != $view_user): ?>
                <a href="action_follow.php?user=<?= urlencode($view_user) ?>" 
                   class="btn <?= $is_following ? 'btn-danger' : 'btn-dark' ?>">
                    <?= $is_following ? 'Unfollow' : 'Follow' ?>
                </a>
                <?php endif; ?>
            </div>
            
            <div class="d-flex mb-4">
                <span class="me-4"><strong><?= $total_following ?></strong> Following</span>
                <span><strong><?= $total_followers ?></strong> Followers</span>
            </div>

            <hr>
            
            <h5>Postingan</h5>
            <?php
            // Ambil postingan user ini dengan JOIN untuk mengambil nama gambar
            $posts = $pdo->prepare("
                SELECT 
                    p.PostID,
                    p.Text,
                    i.ImageName
                FROM post p
                LEFT JOIN detailpost d ON p.PostID = d.PostID
                LEFT JOIN image i ON d.ImageID = i.ImageID
                WHERE p.UserName = ? 
                ORDER BY p.PostID DESC 
                LIMIT 10
            ");
            $posts->execute([$view_user]);
            
            if ($posts->rowCount() > 0) {
                while ($post = $posts->fetch(PDO::FETCH_ASSOC)) {
                    $post_id = $post['PostID'];
                    
                    // --- Logika untuk Like, Comment, Bookmark diabaikan dari snippet ini
                    // --- tetapi Anda harus menempatkannya di sini
                    
                    echo "<div class='border rounded p-3 mb-3 bg-white shadow-sm'>";
                    
                    // TAMPILKAN GAMBAR POSTINGAN
                    if (!empty($post['ImageName'])) {
                        // Asumsi lokasi file gambar adalah: ../asset/img/post/
                        echo "<img src='../asset/img/post/" . htmlspecialchars($post['ImageName']) . "' class='img-fluid rounded mb-2' alt='Post Image'>";
                    }

                    // Tampilkan Teks Post
                    echo "<div>" . htmlspecialchars($post['Text']) . "</div>";
                    
                    // --- Tempatkan Tombol Interaksi (Like, Comment, Bookmark) di sini
                    
                    echo "</div>"; // End of post div
                }
            } else {
                echo "<p class='text-muted'>Pengguna ini belum memiliki postingan.</p>";
            }
            ?>
            
        </div>
        
    </div>
</div>
</body>
</html>