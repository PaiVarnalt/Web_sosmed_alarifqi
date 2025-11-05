<?php
session_start();
include 'lib/koneksi.php';

// Cek apakah user sudah login
if (!isset($_SESSION['username'])) {
  header("Location: modul/login.php");
  exit;
}
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

    <!-- SIDEBAR -->
    <div class="col-md-3 col-lg-2 sidebar border-end">
      <div>
       <div class="px-3 mb-4">
  <i class="fa-brands fa-x-twitter fa-2x"></i>
  <p class="mt-2 fw-bold">@<?= htmlspecialchars($_SESSION['username']) ?></p>
</div>

        <nav class="nav flex-column">
          <a href="index.php"><i class="fa-solid fa-house"></i> <span>Home</span></a>
          <a href="#"><i class="fa-solid fa-magnifying-glass"></i> <span>Explore</span></a>
          <a href="#"><i class="fa-regular fa-bell"></i> <span>Notifications</span></a>
          <a href="#"><i class="fa-regular fa-envelope"></i> <span>Messages</span></a>
          <a href="#"><i class="fa-regular fa-bookmark"></i> <span>Bookmarks</span></a>
          <a href="#"><i class="fa-solid fa-user-group"></i> <span>Communities</span></a>
          <a href="modul/profile.php"><i class="fa-regular fa-user"></i> <span>Profile</span></a>
          <a href="#"><i class="fa-solid fa-gear"></i> <span>Settings</span></a>
        </nav>

        <div class="px-3 mt-4">
          <button class="post-btn"><i class="fa-solid fa-feather"></i> Post</button>
        </div>
      </div>

      <div class="px-3 mt-4">
        <button class="btn btn-outline-secondary w-100" id="darkToggle">
          <i class="fa-solid fa-moon"></i> Mode
        </button>
      </div>
    </div>

    <!-- MAIN FEED -->
    <div class="col-md-6 p-0">
      <!-- Form Posting -->
      <div class="tweet-box bg-light">
        <form action="modul/add_post.php" method="POST" enctype="multipart/form-data">
          <textarea name="content" rows="2" class="form-control border-0 mb-2" placeholder="What is happening?!"></textarea>
          <div class="d-flex justify-content-between align-items-center">
            <input type="file" name="image" class="form-control w-50">
            <button type="submit" class="btn btn-primary rounded-pill px-4"><i class="fa-solid fa-paper-plane"></i> Post</button>
          </div>
        </form>
      </div>

      <!-- Timeline -->
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
    echo "<div class='post-card'>";
    echo "<h6 class='fw-bold mb-1'>@" . htmlspecialchars($p['UserName']) . "</h6>";
    echo "<p class='mb-1'>" . htmlspecialchars($p['Text']) . "</p>";
    
    // tampilkan gambar jika ada
    if (!empty($p['ImageName'])) {
      echo "<img src='asset/img/post/" . htmlspecialchars($p['ImageName']) . "' class='img-fluid rounded mt-2'>";
    }

    echo "<div class='text-muted small mt-2'>
            <i class='fa-regular fa-comment'></i> 0
            <i class='fa-regular fa-heart ms-3'></i> 0
          </div>";
    echo "</div>";
  }
} else {
  echo "<div class='p-3 text-center text-muted'>Belum ada postingan.</div>";
}

 ?>
      </div>
    </div>

    <!-- RIGHT SIDEBAR -->
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
