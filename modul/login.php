<?php
session_start();
include '../lib/koneksi.php';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $loginInput = trim($_POST['username']); // bisa username atau email
  $password = trim($_POST['password']);

  // cek ke database (username atau email)
  $stmt = $pdo->prepare("SELECT * FROM user WHERE UserName = ? OR Email = ?");
  $stmt->execute([$loginInput, $loginInput]);
  $user = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($user && password_verify($password, $user['Password'])) {
    $_SESSION['username'] = $user['UserName'];
    header('Location: ../index.php');
    exit;
  } else {
    $message = '<div class="alert alert-danger">Username / Email atau password salah!</div>';
  }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login - X Clone</title>
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
  <div class="card p-4 shadow w-100" style="max-width: 400px;">
    <h3 class="text-center mb-3">Login ke X Clone</h3>
    <?= $message ?>
    <form method="POST">
      <div class="mb-3">
        <label class="form-label">Username atau Email</label>
        <input type="text" name="username" class="form-control" placeholder="Masukkan username atau email" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" required>
      </div>
      <button class="btn btn-primary w-100">Login</button>
    </form>
    <p class="text-center mt-3">Belum punya akun? <a href="register.php">Daftar</a></p>
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
