<?php include '../lib/koneksi.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Register</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(135deg, #ece9e6, #ffffff);
    }
    .register-card {
      width: 400px;
      background-color: #fff;
      border-radius: 15px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      padding: 2rem;
    }
  </style>
</head>
<body class="d-flex align-items-center justify-content-center vh-100">
  <div class="register-card">
    <h4 class="text-center mb-4 fw-bold">Daftar Akun Baru</h4>
    <form method="POST">
      <div class="row">
        <div class="col-md-6 mb-2">
          <input type="text" name="firstname" class="form-control" placeholder="Nama Depan" required>
        </div>
        <div class="col-md-6 mb-2">
          <input type="text" name="lastname" class="form-control" placeholder="Nama Belakang">
        </div>
      </div>
      <input type="text" name="username" class="form-control mb-2" placeholder="Username" required>
      <input type="email" name="email" class="form-control mb-2" placeholder="Email" required>
      <input type="password" name="password" class="form-control mb-2" placeholder="Password (min. 6 karakter)" required>
      <button name="register" class="btn btn-primary w-100 mt-2">Daftar</button>
    </form>

    <?php
    if (isset($_POST['register'])) {
      $firstname = trim($_POST['firstname']);
      $lastname  = trim($_POST['lastname']);
      $username  = trim($_POST['username']);
      $email     = trim($_POST['email']);
      $password  = $_POST['password'];

      if (strlen($password) < 6) {
        echo "<div class='alert alert-warning mt-3'>Password minimal 6 karakter!</div>";
      } else {
        $cek = mysqli_query($conn, "SELECT * FROM User WHERE username='$username' OR email='$email'");
        if (mysqli_num_rows($cek) > 0) {
          echo "<div class='alert alert-danger mt-3'>Username atau Email sudah digunakan!</div>";
        } else {
          $hash = password_hash($password, PASSWORD_DEFAULT);
          mysqli_query($conn, "INSERT INTO User (firstname, lastname, username, email, password) 
                               VALUES ('$firstname', '$lastname', '$username', '$email', '$hash')");
          echo "<div class='alert alert-success mt-3'>Berhasil daftar! Silakan <a href='login.php'>login</a>.</div>";
        }
      }
    }
    ?>
  </div>
</body>
</html>
