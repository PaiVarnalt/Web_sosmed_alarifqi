<?php
session_start();
include 'lib/koneksi.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>X Clone - Home</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <style>
    :root {
      --light-bg: #ffffff;
      --light-text: #0f1419;
      --dark-bg: #000000;
      --dark-text: #e7e9ea;
    }

    body {
      background-color: var(--light-bg);
      color: var(--light-text);
      transition: background-color .3s, color .3s;
    }

    body.dark-mode {
      background-color: var(--dark-bg);
      color: var(--dark-text);
    }

    .sidebar {
      height: 100vh;
      position: sticky;
      top: 0;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      padding: 1rem 0;
    }

    .sidebar a {
      color: inherit;
      text-decoration: none;
      font-size: 1.2rem;
      font-weight: 500;
      padding: .6rem 1rem;
      border-radius: 50px;
      display: flex;
      align-items: center;
      gap: 1rem;
      transition: background .2s;
    }

    .sidebar a:hover {
      background-color: rgba(29, 155, 240, 0.1);
      color: #1d9bf0;
    }

    .post-btn {
      background-color: #1d9bf0;
      border: none;
      color: #fff;
      font-weight: bold;
      border-radius: 50px;
      padding: .7rem 1rem;
      width: 100%;
      transition: .2s;
    }

    .post-btn:hover {
      background-color: #1a8cd8;
    }

    .tweet-box textarea {
      border: none;
      resize: none;
      font-size: 1.2rem;
      outline: none;
    }

    .tweet-box {
      border-bottom: 1px solid #ddd;
      padding: 1rem;
    }

    .post-card {
      border-bottom: 1px solid #ddd;
      padding: 1rem;
    }

    .post-card img {
      max-height: 400px;
      object-fit: cover;
      border-radius: 16px;
      margin-top: .5rem;
    }
  </style>
</head>
<body>

<div class="container-fluid">
  <div class="row">

    <!-- SIDEBAR -->
    <div class="col-md-3 col-lg-2 sidebar border-end">
      <div>
        <div class="px-3 mb-4">
          <i class="fa-brands fa-x-twitter fa-2x"></i>
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
        $q = mysqli_query($conn, "SELECT * FROM Post ORDER BY PostID DESC");
        if ($q && mysqli_num_rows($q) > 0) {
          while ($p = mysqli_fetch_assoc($q)) {
            echo "<div class='post-card'>";
            echo "<h6 class='fw-bold mb-1'>@" . htmlspecialchars($p['UserName']) . "</h6>";
            echo "<p class='mb-1'>" . htmlspecialchars($p['Content']) . "</p>";
            if (!empty($p['Image'])) {
              echo "<img src='asset/img/post/" . htmlspecialchars($p['Image']) . "' class='img-fluid'>";
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
          <li class="list-group-item">#Bootstrap5</li>
          <li class="list-group-item">#OpenAI</li>
          <li class="list-group-item">#DevCommunity</li>
        </ul>
      </div>
    </div>

  </div>
</div>

<script>
const toggle = document.getElementById('darkToggle');
toggle.addEventListener('click', () => {
  document.body.classList.toggle('dark-mode');
});
</script>

</body>
</html>
