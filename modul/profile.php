<?php include '../lib/koneksi.php'; 
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php'); exit;
}
$id = $_SESSION['user_id'];
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM User WHERE id_user=$id"));
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Profil Saya</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <?php include 'sidebar.php'; ?>
  <div class="main-content">
    <h3>Profil Saya</h3>
    <form method="POST" enctype="multipart/form-data">
      <img src="<?= $user['foto'] ?: '../asset/img/user/default.png' ?>" width="100" class="rounded mb-3">
      <input type="file" name="foto" class="form-control mb-2">
      <input type="text" name="username" class="form-control mb-2" value="<?= $user['username'] ?>">
      <input type="email" name="email" class="form-control mb-2" value="<?= $user['email'] ?>">
      <input type="password" name="password" class="form-control mb-2" placeholder="Ganti password (opsional)">
      <button name="update" class="btn btn-primary">Update</button>
    </form>

    <?php
    if (isset($_POST['update'])) {
      $username = $_POST['username'];
      $email = $_POST['email'];
      $foto = $user['foto'];
      if (!empty($_FILES['foto']['name'])) {
        $foto = '../asset/img/user/' . basename($_FILES['foto']['name']);
        move_uploaded_file($_FILES['foto']['tmp_name'], $foto);
      }
      $setPass = !empty($_POST['password']) ? ", password='" . password_hash($_POST['password'], PASSWORD_DEFAULT) . "'" : "";
      mysqli_query($conn, "UPDATE User SET username='$username', email='$email', foto='$foto' $setPass WHERE id_user=$id");
      echo "<div class='alert alert-success mt-2'>Profil diperbarui!</div>";
    }
    ?>
  </div>
</body>
</html>
