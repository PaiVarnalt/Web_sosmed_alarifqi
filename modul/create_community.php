<?php
session_start();
include '../lib/koneksi.php';
// Pastikan path login.php sudah benar
if (!isset($_SESSION['username'])) header("Location: ../login.php");

$current_user = strtolower(trim($_SESSION['username']));

// Ambil pesan status dari URL jika ada
$status = $_GET['status'] ?? null;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Buat Komunitas Baru</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="../asset/css/stlye.css" rel="stylesheet">
</head>
<body>
<div class="container-fluid">
    <div class="row">

            <?php include 'sidebar.php'; ?>

        
        <div class="col-md-7 p-4 border-end">
            <h3 class="fw-bold mb-4 border-bottom pb-2"><i class="fa-solid fa-plus me-2"></i> Buat Komunitas Baru</h3>

            <?php if ($status === 'success'): ?>
                <div class="alert alert-success">Komunitas berhasil dibuat! Anda sekarang adalah adminnya.</div>
            <?php elseif ($status === 'exists'): ?>
                <div class="alert alert-danger">Nama komunitas ini sudah digunakan. Coba nama lain.</div>
            <?php endif; ?>

            <div class="card p-4">
                <form action="action_create_community.php" method="POST">
                    
                    <div class="mb-3">
                        <label for="communityName" class="form-label fw-bold">Nama Komunitas</label>
                        <input type="text" class="form-control" id="communityName" name="community_name" 
                               placeholder="Contoh: Diskusi PHP Lanjut" required maxlength="100">
                        <div class="form-text">Nama harus unik dan tidak boleh lebih dari 100 karakter.</div>
                    </div>

                    <div class="mb-3">
                        <label for="communityDescription" class="form-label fw-bold">Deskripsi Komunitas</label>
                        <textarea class="form-control" id="communityDescription" name="description" rows="4" 
                                  placeholder="Jelaskan tujuan dan aturan komunitas Anda..." required></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary mt-3">
                        <i class="fa-solid fa-plus-circle me-1"></i> Buat Komunitas
                    </button>
                    <a href="communities.php" class="btn btn-outline-secondary mt-3">Batal</a>
                </form>
            </div>
        </div>

        <div class="col-md-3 p-4">
            <div class="card p-3 bg-light">
                <h6 class="fw-bold">Tips Membuat Komunitas</h6>
                <ul class="small text-muted">
                    <li>Gunakan nama yang jelas dan spesifik.</li>
                    <li>Deskripsi harus menjelaskan topik utama.</li>
                    <li>Pembuat komunitas otomatis menjadi anggota pertama.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
</body>
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
</html>