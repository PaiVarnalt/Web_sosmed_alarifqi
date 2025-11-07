<?php 
session_start();
include '../lib/koneksi.php'; // koneksi pakai $pdo (PDO)

// Cek login
if (!isset($_SESSION['username'])) {
  header("Location: login.php");
  exit;
}

$username = $_SESSION['username'];

// Ambil data user dari database
$stmt = $pdo->prepare("SELECT * FROM user WHERE UserName = ?");
$stmt->execute([$username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
  die("User tidak ditemukan!");
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Profil Saya</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap & FontAwesome -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../asset/css/stlye.css">
</head>
<body class="bg-dark text-light">

  <div class="container-fluid">
    <div class="row">
      <!-- Sidebar -->
      <?php include '../sidebar.php'; ?>

      <!-- Main Content -->
      <div class="col-md-9 col-lg-10 main-content p-4">
        <h3 class="text-light mb-4"><i class="fa-regular fa-user"></i> Profil Saya</h3>

        <form method="POST" enctype="multipart/form-data" class="bg-secondary p-4 rounded shadow-sm">
          <div class="text-center mb-4">
            <img src="<?= htmlspecialchars($user['foto'] ?: '../asset/img/user/default.png') ?>" 
                 width="120" height="120" 
                 class="rounded-circle border border-light mb-3" 
                 alt="Foto Profil">
          </div>

          <div class="mb-3">
            <label class="form-label">Ganti Foto</label>
            <input type="file" name="foto" class="form-control">
          </div>

          <div class="mb-3">
            <label class="form-label">Username</label>
            <input type="text" name="username" class="form-control" 
                   value="<?= htmlspecialchars($user['UserName']) ?>" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" 
                   value="<?= htmlspecialchars($user['Email']) ?>" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Password (opsional)</label>
            <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak ingin mengganti password">
          </div>

          <button type="submit" name="update" class="btn btn-light w-100 fw-bold">
            <i class="fa-solid fa-floppy-disk"></i> Update Profil
          </button>
        </form>

        <?php
        // Proses update profil
        if (isset($_POST['update'])) {
          $newUsername = trim($_POST['username']);
          $email = trim($_POST['email']);
          $foto = $user['foto']; // default foto lama

          if ($newUsername === "" || $email === "") {
            echo "<div class='alert alert-danger mt-3'>⚠️ Username dan Email tidak boleh kosong!</div>";
          } else {
            // Upload foto baru jika ada
            if (!empty($_FILES['foto']['name'])) {
              $uploadDir = realpath(__DIR__ . '/../asset/img/user/') . '/';
              if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

              $fileExt = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
              $fileName = 'user_' . $newUsername . '_' . time() . '.' . $fileExt;
              $targetPath = $uploadDir . $fileName;

              $allowed = ['jpg', 'jpeg', 'png', 'gif'];
              if (in_array($fileExt, $allowed) && move_uploaded_file($_FILES['foto']['tmp_name'], $targetPath)) {
                $foto = '../asset/img/user/' . $fileName;
              }
            }

            // Update dengan atau tanpa password
            if (!empty($_POST['password'])) {
              $hashedPass = password_hash($_POST['password'], PASSWORD_DEFAULT);
              $stmt = $pdo->prepare("UPDATE user SET UserName=?, Email=?, foto=?, Password=? WHERE UserName=?");
              $stmt->execute([$newUsername, $email, $foto, $hashedPass, $username]);
            } else {
              $stmt = $pdo->prepare("UPDATE user SET UserName=?, Email=?, foto=? WHERE UserName=?");
              $stmt->execute([$newUsername, $email, $foto, $username]);
            }

            echo "<div class='alert alert-success mt-3'>✅ Profil berhasil diperbarui!</div>";

            // Update session
            $_SESSION['username'] = $newUsername;
          }
        }
        ?>
      </div>
    </div>
  </div>

</body>
</html>
