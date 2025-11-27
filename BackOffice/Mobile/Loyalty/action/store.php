<?php
include("../../../../Koneksi/koneksi.php");
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php"); exit;
}

// Ambil Data
$uid_akun    = intval($_POST['uid_akun'] ?? 0);
$id_kategori = intval($_POST['id_kategori'] ?? 0);
$id_menu     = intval($_POST['id_menu'] ?? 0); // [UBAH] Ambil ID Menu (Int)
$biji_kopi   = trim($_POST['biji_kopi'] ?? ''); 

// Jika kosong (karena disembunyikan), set jadi NULL untuk Database
if ($biji_kopi === '') $biji_kopi = NULL;

$tanggal = date('Y-m-d');

// Validasi
if ($uid_akun <= 0 || $id_menu <= 0 || $id_kategori <= 0) {
    $_SESSION['error'] = "Data menu wajib dipilih!";
    header("Location: ../add.php?uid=" . $uid_akun);
    exit;
}

try {
    // [UBAH] Query Insert menggunakan id_menu
    $query = "INSERT INTO loyalty (uid_akun, id_kategori, id_menu, biji_kopi, tanggal, status_pengisian) 
              VALUES (?, ?, ?, ?, ?, 'Menunggu Review')";
    
    $stmt = $conn->prepare($query);
    // i i i s s -> (uid, kat, menu, biji, tgl)
    $stmt->bind_param("iiiss", $uid_akun, $id_kategori, $id_menu, $biji_kopi, $tanggal);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Berhasil! Poin ditambahkan.";
        $_SESSION['message_type'] = "success";
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