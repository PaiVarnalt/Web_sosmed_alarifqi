<?php include '../lib/koneksi.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Cari User</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="asset/css/style.css" rel="stylesheet">
</head>
<body>

<div class="sidebar" id="sidebar">
  <button class="toggle-btn" id="toggleBtn">☰</button>
  <nav class="nav flex-column">
    <a href="index.php" class="nav-link">🏠 Home</a>
    <a href="search.php" class="nav-link active">🔍 Cari User</a>
    <a href="profile.php" class="nav-link">👤 Profil</a>
    <a href="login.php" class="nav-link">🔒 Login</a>
  </nav>
</div>

<div class="main-content">
  <h3 class="fw-semibold mb-4">Cari User</h3>

  <form method="GET" class="mb-3">
    <input type="text" name="q" class="form-control w-50" placeholder="Masukkan nama user...">
  </form>

  <div>
    <?php
    if (isset($_GET['q'])) {
      $search = mysqli_real_escape_string($conn, $_GET['q']);
      $qUser = mysqli_query($conn, "
        SELECT UserName, FirstName, LastName, Email
        FROM User
        WHERE FirstName LIKE '%$search%' OR LastName LIKE '%$search%'
      ");
      if (mysqli_num_rows($qUser) > 0) {
        echo "<table class='table table-striped w-75'><thead><tr><th>Username</th><th>Nama</th><th>Email</th></tr></thead><tbody>";
        while ($r = mysqli_fetch_assoc($qUser)) {
          echo "<tr><td>{$r['UserName']}</td><td>{$r['FirstName']} {$r['LastName']}</td><td>{$r['Email']}</td></tr>";
        }
        echo "</tbody></table>";
      } else {
        echo "<p>Tidak ada user ditemukan.</p>";
      }
    }
    ?>
  </div>
</div>

<script>
  const sidebar = document.getElementById('sidebar');
  const toggleBtn = document.getElementById('toggleBtn');
  toggleBtn.addEventListener('click', () => {
    sidebar.classList.toggle('collapsed');
  });
</script>
</body>
</html>
