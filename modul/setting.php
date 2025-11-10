<?php
session_start();
include '../lib/koneksi.php';

if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit;
}

$current_user = strtolower(trim($_SESSION['username']));

// Ambil data profil: MENGGANTI FullName dengan FirstName dan LastName
$q_profile = $pdo->prepare("SELECT UserName, FirstName, LastName, Email FROM user WHERE UserName = ?");
$q_profile->execute([$current_user]);
$profile_data = $q_profile->fetch(PDO::FETCH_ASSOC);

// Ambil pesan status dari URL
$status = $_GET['status'] ?? null;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pengaturan Akun</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="../asset/css/stlye.css" rel="stylesheet">
</head>
<body>
<div class="container-fluid">
    <div class="row">

            <?php include 'sidebar.php'; ?>

        
        <div class="col-md-7 p-4 border-end">
            <h3 class="fw-bold mb-4 border-bottom pb-2"><i class="fa-solid fa-gear me-2"></i> Pengaturan Akun</h3>

            <?php if ($status === 'success'): ?>
                <div class="alert alert-success">Perubahan berhasil disimpan!</div>
            <?php elseif ($status === 'exists'): ?>
                <div class="alert alert-danger">Username tersebut sudah digunakan. Coba yang lain.</div>
            <?php elseif ($status === 'error'): ?>
                <div class="alert alert-danger">Terjadi kesalahan saat menyimpan data.</div>
            <?php endif; ?>

            <div class="card p-4">
                <form action="action_update_profile.php" method="POST">
                    
                    <h5 class="mb-3">Informasi Dasar</h5>

                    <div class="mb-3">
                        <label for="username" class="form-label fw-bold">Username</label>
                        <input type="text" class="form-control" id="username" name="new_username" 
                               value="<?= htmlspecialchars($profile_data['UserName'] ?? '') ?>" 
                               placeholder="Masukkan Username baru" required>
                        <div class="form-text">Username digunakan untuk login dan tag.</div>
                    </div>

                    <div class="mb-3">
                        <label for="firstname" class="form-label fw-bold">Nama Depan</label>
                        <input type="text" class="form-control" id="firstname" name="first_name" 
                               value="<?= htmlspecialchars($profile_data['FirstName'] ?? '') ?>" 
                               placeholder="Nama Depan Anda" required>
                    </div>

                    <div class="mb-3">
                        <label for="lastname" class="form-label fw-bold">Nama Belakang (Opsional)</label>
                        <input type="text" class="form-control" id="lastname" name="last_name" 
                               value="<?= htmlspecialchars($profile_data['LastName'] ?? '') ?>" 
                               placeholder="Nama Belakang Anda">
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label fw-bold">Email</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?= htmlspecialchars($profile_data['Email'] ?? '') ?>" 
                               placeholder="Alamat Email Anda" required>
                    </div>

                    <button type="submit" class="btn btn-primary mt-3">
                        <i class="fa-solid fa-save me-1"></i> Simpan Perubahan
                    </button>
                </form>
            </div>
        </div>

        <div class="col-md-3 p-4">
            <div class="list-group">
                <a href="#" class="list-group-item list-group-item-action active">Informasi Profil</a>
                <a href="logout.php" class="list-group-item list-group-item-action text-danger fw-bold mt-3">
            <i class="fa-solid fa-right-from-bracket me-2"></i> Logout
        </a>
            </div>
        </div>
        
    </div>
</div>

<script>
const toggle = document.getElementById('darkToggle');
if(toggle){
  toggle.addEventListener('click', () => {
    document.body.classList.toggle('dark-mode');
    localStorage.setItem('theme', document.body.classList.contains('dark-mode') ? 'dark-mode' : '');
  });
}

if(localStorage.getItem('theme') === 'dark-mode'){
  document.body.classList.add('dark-mode');
}
</script>
</body>
</html>