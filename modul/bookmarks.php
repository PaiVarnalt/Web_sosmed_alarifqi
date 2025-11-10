<?php
session_start();
// Pastikan path ke koneksi.php sudah benar dari dalam folder modul/
include '../lib/koneksi.php'; 

if (!isset($_SESSION['username'])) {
  header("Location: login.php");
  exit;
}

$current_user_name = $_SESSION['username'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Bookmarks - X Clone</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link href="../asset/css/stlye.css" rel="stylesheet"> 
</head>
<body class="bg-light">

<div class="container-fluid">
    <div class="row">
        

            <?php include 'sidebar.php'; ?>

        
        <div class="col-md-6 p-3 border-start border-end"> 
            <h4 class="fw-bold mb-4"><i class="fa-solid fa-bookmark"></i> Postingan Tersimpan</h4>

            <div>
                <?php
                // Query untuk mengambil semua postingan yang dibookmark oleh user saat ini
                $q = $pdo->prepare("
                    SELECT 
                        p.PostID,
                        p.UserName AS PostUserName,
                        p.Text,
                        p.Date,
                        p.Time,
                        i.ImageName
                    FROM bookmarks b
                    JOIN post p ON b.PostID = p.PostID
                    LEFT JOIN detailpost d ON p.PostID = d.PostID
                    LEFT JOIN image i ON d.ImageID = i.ImageID
                    WHERE b.UserName = ?
                    ORDER BY b.CreatedAt DESC
                ");
                $q->execute([$current_user_name]);

                if ($q->rowCount() > 0) {
                    while ($p = $q->fetch(PDO::FETCH_ASSOC)) {
                        $post_id = $p['PostID'];

                        // --- LOGIKA INTERAKSI (LIKE & BOOKMARK) ---
                        
                        // Hitung jumlah likes dan komentar
                        $likeCount = $pdo->query("SELECT COUNT(*) FROM likes WHERE PostID={$post_id}")->fetchColumn();
                        $commentCount = $pdo->query("SELECT COUNT(*) FROM comment WHERE PostID={$post_id}")->fetchColumn();

                        // Cek status Like
                        $liked = $pdo->prepare("SELECT * FROM likes WHERE PostID=? AND UserName=?");
                        $liked->execute([$post_id, $current_user_name]); 
                        $isLiked = $liked->rowCount() > 0;

                        // Cek status Bookmark (selalu TRUE di halaman ini, tapi tetap dihitung untuk tampilan tombol)
                        $bookmarked = $pdo->prepare("SELECT * FROM bookmarks WHERE PostID=? AND UserName=?");
                        $bookmarked->execute([$post_id, $current_user_name]); 
                        $isBookmarked = $bookmarked->rowCount() > 0;

                        
                        // --- TAMPILAN POSTINGAN ---
                        echo "<div class='post-card p-3 border rounded mb-3 bg-white shadow-sm'>";
                        echo "<h6 class='fw-bold mb-1'>@" . htmlspecialchars($p['PostUserName']) . "</h6>";
                        echo "<p class='mb-1'>" . htmlspecialchars($p['Text']) . "</p>";
                        
                        // tampilkan gambar jika ada
                        if (!empty($p['ImageName'])) {
                            echo "<img src='../asset/img/post/" . htmlspecialchars($p['ImageName']) . "' class='img-fluid rounded mt-2'>";
                        }

                        // Tombol like & komentar & bookmark
                        echo "<div class='d-flex justify-content-between align-items-center mt-2'>
                                <div class='d-flex'>"; // Wrapper untuk Like dan Bookmark
                                
                        // Tombol LIKE
                        echo "
                                <form action='like_post.php' method='POST' class='me-3'>
                                    <input type='hidden' name='postID' value='{$post_id}'>
                                    <button type='submit' class='btn btn-sm " . ($isLiked ? "btn-danger" : "btn-outline-danger") . "'>
                                        <i class='fa-solid fa-heart'></i> $likeCount
                                    </button>
                                </form>";
                                
                        // Tombol BOOKMARK (Dengan class yang menunjukkan sudah disimpan)
                        $bookmark_class = $isBookmarked ? 'btn-primary' : 'btn-outline-secondary';
                        $bookmark_icon = $isBookmarked ? 'fa-solid' : 'fa-regular';

                        echo "
                                <form action='bookmark_post.php' method='POST'>
                                    <input type='hidden' name='postID' value='{$post_id}'>
                                    <button type='submit' class='btn btn-sm {$bookmark_class}'>
                                        <i class='{$bookmark_icon} fa-bookmark'></i>
                                    </button>
                                </form>
                                </div>"; // End wrapper

                        // Kolom Komentar
                        echo "<span class='text-muted'><i class='fa-regular fa-comment'></i> $commentCount</span>
                            </div>";
                        
                        // Form komentar (bisa ditambahkan jika Anda ingin user bisa komentar di halaman bookmarks)
                        // ...
                        
                        echo "</div>"; // End of post div
                    }
                } else {
                    echo "<div class='p-5 text-center text-muted border rounded bg-white'>Anda belum memiliki postingan yang disimpan.</div>";
                }
                ?>
            </div>
            
        </div>

        <div class="col-md-3 p-3 border-start">
            <div class="mb-3">
                <input type="text" class="form-control" placeholder="Search user...">
             </div>
             <div class="card">
                <div class="card-header fw-bold">Trends for you</div>
                <ul class="list-group list-group-flush">
                  <li class="list-group-item">#CodingLife</li>
                </ul>
             </div>
        </div>
        
    </div>
</div>
</body>
</html>