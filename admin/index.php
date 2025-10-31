<?php include 'lib/koneksi.php'; ?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body {
      background-color: #f8f9fa;
    }
    .sidebar {
      height: 100vh;
      background-color: #212529;
      color: #fff;
      position: fixed;
      width: 240px;
      padding-top: 20px;
    }
    .sidebar a {
      color: #adb5bd;
      text-decoration: none;
      display: block;
      padding: 10px 20px;
      border-radius: 5px;
    }
    .sidebar a:hover, .sidebar a.active {
      background-color: #495057;
      color: #fff;
    }
    .content {
      margin-left: 260px;
      padding: 20px;
    }
    .card-summary {
      border: none;
      border-radius: 12px;
      box-shadow: 0 3px 8px rgba(0,0,0,0.1);
    }
    img.preview {
      width: 60px;
      height: 60px;
      object-fit: cover;
      border-radius: 8px;
    }
  </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
  <div class="px-3 mb-4">
    <h4 class="fw-bold text-center text-light">Admin Panel</h4>
  </div>
  <a href="#user" class="active">👤 Data User</a>
  <a href="#post">📝 Data Postingan</a>
  <a href="#chart">📊 Statistik</a>
</div>

<!-- Main Content -->
<div class="content">
  <h2 class="fw-semibold mb-4">Dashboard</h2>

  <?php
  $totalUser = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM User"))['total'];
  $totalPost = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM Post"))['total'];
  ?>

  <div class="row mb-4">
    <div class="col-md-6">
      <div class="card card-summary text-center p-3 bg-white">
        <h5 class="text-muted">Total User</h5>
        <h2 class="fw-bold text-primary"><?= $totalUser ?></h2>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card card-summary text-center p-3 bg-white">
        <h5 class="text-muted">Total Postingan</h5>
        <h2 class="fw-bold text-success"><?= $totalPost ?></h2>
      </div>
    </div>
  </div>

  <!-- =================== -->
  <!-- Data User Section -->
  <!-- =================== -->
  <section id="user" class="mb-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h4 class="fw-semibold mb-0">👤 Data User</h4>
      <input type="text" id="searchUser" class="form-control w-25" placeholder="Cari user...">
    </div>
    <div class="table-responsive bg-white shadow-sm rounded p-3">
      <table class="table table-striped align-middle" id="tableUser">
        <thead class="table-dark">
          <tr>
            <th>No</th>
            <th>Username</th>
            <th>Nama Lengkap</th>
            <th>Email</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $qUser = mysqli_query($conn, "SELECT UserName, FirstName, LastName, Email FROM User");
          $no = 1;
          while ($row = mysqli_fetch_assoc($qUser)) {
            echo "
              <tr>
                <td>{$no}</td>
                <td>{$row['UserName']}</td>
                <td>{$row['FirstName']} {$row['LastName']}</td>
                <td>{$row['Email']}</td>
              </tr>
            ";
            $no++;
          }
          ?>
        </tbody>
      </table>
    </div>
  </section>

  <!-- =================== -->
  <!-- Data Post Section -->
  <!-- =================== -->
  <section id="post" class="mb-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h4 class="fw-semibold mb-0">📝 Data Postingan</h4>
      <input type="text" id="searchPost" class="form-control w-25" placeholder="Cari postingan...">
    </div>
    <div class="table-responsive bg-white shadow-sm rounded p-3">
      <table class="table table-striped align-middle" id="tablePost">
        <thead class="table-dark">
          <tr>
            <th>No</th>
            <th>User</th>
            <th>Tanggal</th>
            <th>Isi Post</th>
            <th>Komentar</th>
            <th>Gambar</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $qPost = mysqli_query($conn, "
            SELECT 
              P.PostID, P.Date, P.Text, P.UserName,
              U.FirstName, U.LastName,
              COUNT(DISTINCT D.Comment) AS JumlahKomentar,
              COUNT(DISTINCT D.ImageID) AS JumlahGambar
            FROM Post P
            JOIN User U ON P.UserName = U.UserName
            LEFT JOIN DetailPost D ON P.PostID = D.PostID
            GROUP BY P.PostID
            ORDER BY P.PostID DESC
          ");
          $no = 1;
          while ($row = mysqli_fetch_assoc($qPost)) {
            // Ambil satu gambar (kalau ada)
            $gambar = mysqli_query($conn, "
              SELECT I.ImageName 
              FROM DetailPost D
              JOIN Image I ON D.ImageID = I.ImageID
              WHERE D.PostID = {$row['PostID']}
              LIMIT 1
            ");
            $img = mysqli_fetch_assoc($gambar);
            $imgPreview = $img ? "<img src='asset/img/post/{$img['ImageName']}' class='preview'>" : "-";

            echo "
              <tr>
                <td>{$no}</td>
                <td>{$row['FirstName']} {$row['LastName']}</td>
                <td>{$row['Date']}</td>
                <td>{$row['Text']}</td>
                <td><span class='badge bg-primary'>{$row['JumlahKomentar']}</span></td>
                <td>{$imgPreview}</td>
              </tr>
            ";
            $no++;
          }
          ?>
        </tbody>
      </table>
    </div>
  </section>

  <!-- =================== -->
  <!-- Chart Section -->
  <!-- =================== -->
  <section id="chart" class="mb-5">
    <h4 class="fw-semibold mb-3">📊 Jumlah Postingan per User</h4>
    <div class="card p-3 bg-white shadow-sm rounded">
      <canvas id="postChart" height="100"></canvas>
    </div>
  </section>

  <?php
  // Data chart: jumlah post per user
  $chartQuery = mysqli_query($conn, "
    SELECT U.UserName, COUNT(P.PostID) AS TotalPost
    FROM User U
    LEFT JOIN Post P ON U.UserName = P.UserName
    GROUP BY U.UserName
  ");
  $labels = [];
  $data = [];
  while ($row = mysqli_fetch_assoc($chartQuery)) {
    $labels[] = $row['UserName'];
    $data[] = $row['TotalPost'];
  }
  ?>

</div>

<script>
  // Chart.js render
  const ctx = document.getElementById('postChart');
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: <?= json_encode($labels) ?>,
      datasets: [{
        label: 'Jumlah Postingan',
        data: <?= json_encode($data) ?>,
        borderWidth: 1,
        backgroundColor: '#0d6efd'
      }]
    },
    options: {
      scales: { y: { beginAtZero: true } }
    }
  });

  // Search User
  document.getElementById('searchUser').addEventListener('keyup', function() {
    let filter = this.value.toLowerCase();
    document.querySelectorAll('#tableUser tbody tr').forEach(row => {
      row.style.display = row.textContent.toLowerCase().includes(filter) ? '' : 'none';
    });
  });

  // Search Post
  document.getElementById('searchPost').addEventListener('keyup', function() {
    let filter = this.value.toLowerCase();
    document.querySelectorAll('#tablePost tbody tr').forEach(row => {
      row.style.display = row.textContent.toLowerCase().includes(filter) ? '' : 'none';
    });
  });
</script>

</body>
</html>
