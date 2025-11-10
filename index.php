<?php
session_start();
include 'lib/koneksi.php';

// Cek apakah user sudah login
if (!isset($_SESSION['username'])) {
  header("Location: modul/login.php");
  exit;
}

// Ambil username yang sedang login (digunakan untuk cek like/bookmark)
$current_user_name = $_SESSION['username']; 
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>X Clone - Home</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="asset/css/stlye.css">
</head>
<body>

<div class="container-fluid">
  <div class="row">
  <?php 
  // Asumsi sidebar.php tidak memiliki wrapper kolom, maka ditambahkan di sini

  include 'sidebar.php';

  ?>
    

    <div class="col-md-6 p-0 border-start border-end">
      <div class="tweet-box bg-light p-3">
        <form action="modul/add_post.php" method="POST" enctype="multipart/form-data">
          <textarea name="content" rows="2" class="form-control border-0 mb-2" placeholder="What is happening?!"></textarea>
          <div class="d-flex justify-content-between align-items-center">
            <input type="file" name="image" class="form-control w-50">
            <button type="submit" class="btn btn-primary rounded-pill px-4"><i class="fa-solid fa-paper-plane"></i> Post</button>
          </div>
        </form>
      </div>
      <hr class="my-0">

      <div>
        <?php
    $q = $pdo->query("
      SELECT 
        p.PostID,
        p.UserName,
        p.Text,
        p.Date,
        p.Time,
        i.ImageName
      FROM post p
      LEFT JOIN detailpost d ON p.PostID = d.PostID
      LEFT JOIN image i ON d.ImageID = i.ImageID
      ORDER BY p.PostID DESC
    ");

if ($q && $q->rowCount() > 0) {
  while ($p = $q->fetch(PDO::FETCH_ASSOC)) {
    echo "<div class='post-card p-3 border-bottom'>";
    echo "<h6 class='fw-bold mb-1'>@" . htmlspecialchars($p['UserName']) . "</h6>";
    echo "<p class='mb-1'>" . htmlspecialchars($p['Text']) . "</p>";
    
    // tampilkan gambar jika ada
    if (!empty($p['ImageName'])) {
      echo "<img src='asset/img/post/" . htmlspecialchars($p['ImageName']) . "' class='img-fluid rounded mt-2'>";
    }

// Hitung jumlah likes dan komentar
$likeCount = $pdo->query("SELECT COUNT(*) FROM likes WHERE PostID=" . $p['PostID'])->fetchColumn();
$commentCount = $pdo->query("SELECT COUNT(*) FROM comment WHERE PostID=" . $p['PostID'])->fetchColumn();

// Cek status Like
$liked = $pdo->prepare("SELECT * FROM likes WHERE PostID=? AND UserName=?");
$liked->execute([$p['PostID'], $current_user_name]); 
$isLiked = $liked->rowCount() > 0;

// Cek status Bookmark (BARU DITAMBAH)
$bookmarked = $pdo->prepare("SELECT * FROM bookmarks WHERE PostID=? AND UserName=?");
$bookmarked->execute([$p['PostID'], $current_user_name]); 
$isBookmarked = $bookmarked->rowCount() > 0;

// Tombol like & komentar & bookmark
echo "<div class='d-flex justify-content-between align-items-center mt-2'>
        <div class='d-flex'>"; // Wrapper untuk Like dan Bookmark
        
// Tombol LIKE
echo "
        <form action='modul/like_post.php' method='POST' class='me-3'>
            <input type='hidden' name='postID' value='{$p['PostID']}'>
            <button type='submit' class='btn btn-sm " . ($isLiked ? "btn-danger" : "btn-outline-danger") . "'>
                <i class='fa-solid fa-heart'></i> $likeCount
            </button>
        </form>";
        
// Tombol BOOKMARK
$bookmark_class = $isBookmarked ? 'btn-primary' : 'btn-outline-secondary';
$bookmark_icon = $isBookmarked ? 'fa-solid' : 'fa-regular';

echo "
        <form action='modul/bookmark_post.php' method='POST'>
            <input type='hidden' name='postID' value='{$p['PostID']}'>
            <button type='submit' class='btn btn-sm {$bookmark_class}'>
                <i class='{$bookmark_icon} fa-bookmark'></i>
            </button>
        </form>
        </div>"; // End wrapper

// Kolom Komentar
echo "<span class='text-muted'><i class='fa-regular fa-comment'></i> $commentCount</span>
      </div>";

// Form komentar
echo "
<form action='modul/add_comment.php' method='POST' class='mt-2'>
  <input type='hidden' name='postID' value='{$p['PostID']}'>
  <div class='input-group input-group-sm'>
    <input type='text' name='comment' class='form-control' placeholder='Tulis komentar...'>
    <button class='btn btn-primary'>Kirim</button>
  </div>
</form>
";

// Tampilkan komentar
$comments = $pdo->query("
  SELECT c.comment, u.username 
  FROM comment c 
  JOIN user u ON c.UserName = u.UserName 
  WHERE c.PostID = {$p['PostID']} 
  ORDER BY c.CommentID DESC
");
if ($comments->rowCount() > 0) {
  echo "<div class='mt-2 ps-2'>";
  while ($c = $comments->fetch(PDO::FETCH_ASSOC)) {
    echo "<div class='border-start ps-2 mb-1'>
            <strong>@{$c['username']}</strong> " . htmlspecialchars($c['comment']) . "
          </div>";
  }
  echo "</div>";
}

    echo "</div>";
  }
} else {
  echo "<div class='p-3 text-center text-muted'>Belum ada postingan.</div>";
}

  ?>
      </div>
    </div>

    <div class="col-md-3 border-start p-3">
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