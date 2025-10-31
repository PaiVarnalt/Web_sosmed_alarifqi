<?php include '../lib/koneksi.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="d-flex align-items-center justify-content-center vh-100">
  <form method="POST" class="p-4 rounded shadow bg-light" style="width:350px;">
    <h4 class="mb-3 text-center">Login</h4>
    <input type="text" name="username" class="form-control mb-2" placeholder="Username" required>
    <input type="password" name="password" class="form-control mb-2" placeholder="Password" required>
    <button name="login" class="btn btn-primary w-100">Masuk</button>
    <p class="mt-3 text-center"><a href="register.php">Belum punya akun?</a></p>

    <?php
    if (isset($_POST['login'])) {
  $user = $_POST['username'] ?? '';
  $pass = $_POST['password'] ?? '';

  $q = mysqli_query($conn, "SELECT * FROM User WHERE UserName='$user' OR Email='$user'");
  $d = mysqli_fetch_assoc($q);

  if ($d) {
    if (password_verify($pass, $d['Password'])) {
      $_SESSION['user_id'] = $d['UserName']; // atau id kalau kamu punya kolom id
      header("Location: ../index.php");
      exit;
    } else {
      echo "<div class='alert alert-danger mt-3'>Password salah!</div>";
    }
  } else {
    echo "<div class='alert alert-warning mt-3'>User tidak ditemukan!</div>";
  }
}


    ?>
  </form>
</body>
</html>
