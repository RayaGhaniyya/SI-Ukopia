<?php
session_start();
include("../../../Koneksi/koneksi.php");

// --- KONFIGURASI PENTING (DIPERBARUI) ---
$BASE_URL = "http://localhost/SI-Ukopia/BackOffice/Mobile/Uploads/Menu/"; 
$UPLOAD_DIR = '../Uploads/Menu/'; 
// --- SELESAI KONFIGURASI ---

if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['message_type'] = 'error';
    $_SESSION['message'] = 'ID Menu tidak valid.';
    header("Location: index.php");
    exit;
}

$id_menu = (int)$_GET['id'];

// --- Hapus file gambar dari server ---
$sql_select = "SELECT gambar_url FROM menu WHERE id_menu = ?";
$stmt_select = $conn->prepare($sql_select);
$stmt_select->bind_param("i", $id_menu);
$stmt_select->execute();
$result = $stmt_select->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $gambar_url = $row['gambar_url'];
    
    $fileName = str_replace($BASE_URL, '', $gambar_url);
    $filePath = $UPLOAD_DIR . $fileName;
    if (file_exists($filePath)) {
        @unlink($filePath);
    }
}
$stmt_select->close();
// --- Selesai Hapus File ---

// Query DELETE
$sql_delete = "DELETE FROM menu WHERE id_menu = ?";
$stmt_delete = $conn->prepare($sql_delete);

if ($stmt_delete) {
    $stmt_delete->bind_param("i", $id_menu);
    
    if ($stmt_delete->execute()) {
        $_SESSION['message_type'] = 'success';
        $_SESSION['message'] = 'Menu berhasil dihapus.';
    } else {
        $_SESSION['message_type'] = 'error';
        $_SESSION['message'] = 'Gagal menghapus data: ' . $stmt_delete->error;
    }
    $stmt_delete->close();
} else {
    $_SESSION['message_type'] = 'error';
    $_SESSION['message'] = 'Gagal mempersiapkan query: ' . $conn->error;
}

$conn->close();
header("Location: index.php");
exit;
?>