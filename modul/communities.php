<?php
session_start();
include '../lib/koneksi.php';
if (!isset($_SESSION['username'])) header("Location: login.php");
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Communities - X Clone</title>
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link href="../asset/css/stlye.css" rel="stylesheet">
</head>
<body>
<div class="container-fluid">
  <div class="row">
    <?php include 'sidebar.php'; ?>
    <div class="col-md-6 p-3">
      <h4 class="fw-bold mb-3">Communities</h4>
      <p class="text-muted">Belum ada komunitas aktif.</p>
    </div>
  </div>
</div>
</body>
</html>
