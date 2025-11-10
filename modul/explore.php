<?php
session_start();
include '../lib/koneksi.php'; // Path ke koneksi diperbaiki karena explore.php ada di modul/

if (!isset($_SESSION['username'])) {
  header("Location: login.php");
  exit;
}

$search = trim($_GET['q'] ?? '');
?>

<!DOCTYPE html>
<html lang="id">
<head>
 <meta charset="UTF-8">
 <title>Explore - X Clone</title>
 <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
 <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
 <link href="../asset/css/stlye.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container-fluid">
 <div class="row">
  <?php 
    // Asumsi sidebar.php ada di direktori yang sama (modul/)
    // Jika sidebar.php tidak memiliki wrapper kolom, Anda perlu menambahkannya seperti di index.php
    include 'sidebar.php'; 
    ?>

  <div class="col-md-6 p-3">
<h4 class="fw-bold mb-3"><i class="fa-solid fa-hashtag"></i> Explore</h4>

<form method="get" class="mb-3 d-flex">
 <input type="text" name="q" class="form-control me-2" placeholder="Cari user, post, atau komunitas..." value="<?= htmlspecialchars($search) ?>">
 <button class="btn btn-dark" type="submit"><i class="fa fa-search"></i></button>
</form>

<?php
if ($search != '') {
 echo "<h6 class='text-muted mb-3'>Hasil pencarian untuk: <strong>" . htmlspecialchars($search) . "</strong></h6>";

 // 🔍 Cari user
 $u = $pdo->prepare("SELECT * FROM user WHERE UserName LIKE ? OR FirstName LIKE ? OR LastName LIKE ? LIMIT 10");
 $u->execute(["%$search%", "%$search%", "%$search%"]); 

 if ($u->rowCount() > 0) {
  echo "<h5 class='mt-3 mb-2'>👤 Pengguna</h5>";
  while ($user = $u->fetch(PDO::FETCH_ASSOC)) {
// Menggunakan 'foto' sesuai nama kolom di database Anda
$foto = !empty($user['foto']) ? '../asset/img/user' . $user['foto'] : '../asset/img/user/default.jpg';

$namaLengkap = $user['FirstName'] . ' ' . $user['LastName'];

echo "
            <a href='profile.php?u=" . urlencode($user['UserName']) . "' class='text-decoration-none'>
<div class='d-flex align-items-center border rounded p-2 mb-2 bg-white shadow-sm'>
 <img src='{$foto}' width='45' height='45' class='rounded-circle me-2'>
 <div>
  <strong>@{$user['UserName']}</strong><br>
  <small class='text-muted'>{$namaLengkap}</small>
 </div>
</div>
            </a>";
  }
 }

 // 🔍 Cari komunitas (Tidak berubah)
 $c = $pdo->prepare("SELECT * FROM communities WHERE CommunityName LIKE ? LIMIT 10");
 $c->execute(["%$search%"]);
 if ($c->rowCount() > 0) {
  echo "<h5 class='mt-4 mb-2'>👥 Komunitas</h5>";
  while ($com = $c->fetch(PDO::FETCH_ASSOC)) {
echo "
<div class='border rounded p-2 mb-2 bg-white shadow-sm'>
 <strong>{$com['CommunityName']}</strong><br>
 <small class='text-muted'>{$com['Description']}</small>
</div>";
  }
 }

 // 🔍 Cari posting (Tidak berubah)
 $p = $pdo->prepare("SELECT * FROM post WHERE Text LIKE ? LIMIT 10");
 $p->execute(["%$search%"]);
 if ($p->rowCount() > 0) {
  echo "<h5 class='mt-4 mb-2'>📝 Postingan</h5>";
  while ($post = $p->fetch(PDO::FETCH_ASSOC)) {
echo "
<div class='border rounded p-2 mb-2 bg-white shadow-sm'>
 <strong>@{$post['UserName']}</strong><br>
 " . htmlspecialchars($post['Text']) . "
</div>";
  }
 }

 if ($u->rowCount() == 0 && $c->rowCount() == 0 && $p->rowCount() == 0) {
  echo "<p class='text-muted'>Tidak ada hasil ditemukan.</p>";
 }
} else {
 // ✨ Tampilan default kalau belum mencari (Trending Post)
 echo "<h5 class='fw-bold mb-3'>🔥 Trending Post</h5>";
 $q = $pdo->query("SELECT * FROM post ORDER BY PostID DESC LIMIT 10");
 while ($p = $q->fetch(PDO::FETCH_ASSOC)) {
  echo "<div class='border rounded p-2 mb-2 bg-white shadow-sm'>
<strong>@{$p['UserName']}</strong><br>" .
htmlspecialchars($p['Text']) .
  "</div>";
 }
}
 ?>
</div>
 </div>
</div>

</body>
</html>