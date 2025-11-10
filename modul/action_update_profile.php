<?php
session_start();
include '../lib/koneksi.php'; 

if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit;
}

$old_user_raw = trim($_SESSION['username']);
$old_user = strtolower($old_user_raw);

// Data yang diterima dari form
$new_user_raw = trim($_POST['new_username'] ?? '');
$first_name = trim($_POST['first_name'] ?? ''); // Dapatkan First Name
$last_name = trim($_POST['last_name'] ?? '');   // Dapatkan Last Name (bisa kosong)
$email = trim($_POST['email'] ?? '');

// Standarisasi case untuk username baru
$new_user = strtolower($new_user_raw);

if (empty($new_user) || empty($first_name) || empty($email)) {
    header("Location: settings.php?status=error");
    exit;
}

try {
    // 1. CEK KETERSEDIAAN USERNAME (Hanya jika username berubah)
    if ($new_user !== $old_user) {
        $q_check = $pdo->prepare("SELECT COUNT(*) FROM user WHERE UserName = ?");
        $q_check->execute([$new_user]);
        if ($q_check->fetchColumn() > 0) {
            header("Location: settings.php?status=exists");
            exit;
        }
    }

    // 2. UPDATE DATA di tabel 'user'
    $q_update = $pdo->prepare("
        UPDATE user 
        SET UserName = ?, FirstName = ?, LastName = ?, Email = ?
        WHERE UserName = ?
    ");
    // Eksekusi query dengan 5 parameter
    $q_update->execute([$new_user, $first_name, $last_name, $email, $old_user]);

    // 3. JIKA USERNAME BERUBAH, PERBARUI SESSION
    if ($new_user !== $old_user) {
        // Penting: Memastikan ON UPDATE CASCADE sudah diatur di database!
        $_SESSION['username'] = $new_user; 
    }

    header("Location: settings.php?status=success");
    exit;

} catch (PDOException $e) {
    // Tangani error duplicate entry atau error lainnya
    if ($e->getCode() === '23000' || strpos($e->getMessage(), '1062') !== false) {
        header("Location: settings.php?status=exists");
        exit;
    }
    
    // error_log("Error updating profile: " . $e->getMessage()); 
    header("Location: settings.php?status=error");
    exit;
}
?>