<?php
include("../../../../Koneksi/koneksi.php");
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php");
    exit;
}

// Ambil Data
$uid_akun    = intval($_POST['uid_akun'] ?? 0);
$nama_menu   = trim($_POST['nama_menu'] ?? '');
$id_kategori = intval($_POST['id_kategori'] ?? 0);
$biji_kopi   = trim($_POST['biji_kopi'] ?? '');
$tanggal     = date('Y-m-d'); // Tanggal hari ini

// Validasi Sederhana
if ($uid_akun <= 0 || empty($nama_menu) || $id_kategori <= 0 || empty($biji_kopi)) {
    echo "<script>alert('Data tidak lengkap!'); window.history.back();</script>";
    exit;
}

try {
    // Query Insert
    // Kolom nilai (keasaman, dll) dibiarkan default 0
    // Status default 'Menunggu Review'
    $query = "INSERT INTO loyalty (uid_akun, id_kategori, nama_menu, biji_kopi, tanggal, status_pengisian) 
              VALUES (?, ?, ?, ?, ?, 'Menunggu Review')";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iisss", $uid_akun, $id_kategori, $nama_menu, $biji_kopi, $tanggal);
    
    if ($stmt->execute()) {
        // Sukses
        $_SESSION['message'] = "Berhasil! Menu telah masuk ke akun customer.";
        $_SESSION['message_type'] = "success";
        
        // Redirect kembali ke halaman pencarian untuk input customer lain
        header("Location: ../index.php");
    } else {
        throw new Exception("Gagal menyimpan data.");
    }
    
    $stmt->close();

} catch (Exception $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
    header("Location: ../add.php?uid=" . $uid_akun);
}

$conn->close();
?>