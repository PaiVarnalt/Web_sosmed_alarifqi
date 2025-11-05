<?php
session_start();
include '../lib/koneksi.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username']);
  $first = trim($_POST['firstname']);
  $last = trim($_POST['lastname']);
  $email = trim($_POST['email']);
  $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

  // Cek user sudah ada
  $check = $pdo->prepare("SELECT * FROM user WHERE UserName = ?");
  $check->execute([$username]);

  if ($check->rowCount() > 0) {
    $message = '<div class="alert alert-danger">Username sudah digunakan!</div>';
  } else {
    $stmt = $pdo->prepare("INSERT INTO user (UserName, FirstName, LastName, Email, Password) VALUES (?, ?, ?, ?, ?)");
    if ($stmt->execute([$username, $first, $last, $email, $password])) {
      $message = '<div class="alert alert-success">Registrasi berhasil! <a href="login.php">Login sekarang</a>.</div>';
    } else {
      $message = '<div class="alert alert-danger">Terjadi kesalahan, coba lagi.</div>';
    }
  }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Register - X Clone</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { transition: .3s; }
    body.dark-mode { background-color: #000; color: #e7e9ea; }
    .card { border-radius: 20px; }
    body.dark-mode .card { background-color: #121212; border: 1px solid #333; }
    body.dark-mode .form-control { background-color: #1e1e1e; color: #fff; border-color: #333; }
    body.dark-mode .btn-primary { background-color: #1d9bf0; border: none; }
    .toggle-dark { position: fixed; top: 1rem; right: 1rem; }
  </style>
</head>
<body>
<div class="toggle-dark">
  <button id="darkToggle" class="btn btn-outline-light btn-sm"><i class="fa-solid fa-moon"></i></button>
</div>

<div class="container d-flex align-items-center justify-content-center vh-100">
  <div class="card p-4 shadow w-100" style="max-width: 420px;">
    <h3 class="text-center mb-3">Buat Akun Baru</h3>
    <?= $message ?>
    <form method="POST">
      <div class="mb-3">
        <label class="form-label">Username</label>
        <input type="text" name="username" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Nama Depan</label>
        <input type="text" name="firstname" class="form-control">
      </div>
      <div class="mb-3">
        <label class="form-label">Nama Belakang</label>
        <input type="text" name="lastname" class="form-control">
      </div>
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control">
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" required>
      </div>
      <button class="btn btn-primary w-100">Daftar</button>
    </form>
    <p class="text-center mt-3">Sudah punya akun? <a href="login.php">Login</a></p>
  </div>
</div>

<script>
const toggle = document.getElementById('darkToggle');
if(localStorage.getItem('theme') === 'dark'){ document.body.classList.add('dark-mode'); }
toggle.addEventListener('click', () => {
  document.body.classList.toggle('dark-mode');
  localStorage.setItem('theme', document.body.classList.contains('dark-mode') ? 'dark' : 'light');
});
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js"></script>
</body>
</html>
