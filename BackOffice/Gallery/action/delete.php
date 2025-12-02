<?php
include("../../../Koneksi/koneksi.php");
// Hapus include head.php dan session.php (tidak perlu untuk file JSON)
// include("../../Component/session.php");
// include("../../Component/head.php");
header('Content-Type: application/json');

// Mulai session HANYA untuk cek login admin (jika perlu)
session_start();
// if (!isset($_SESSION['admin_username'])) {
//     echo json_encode(['success' => false, 'message' => 'Akses ditolak']);
//     exit;
// }


// Validasi method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method tidak valid']);
    exit;
}

// Validasi input
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID galeri tidak valid!']);
    exit;
}

// Mulai transaksi
$conn->begin_transaction();

try {
    // Cek apakah data exists
    $stmt_check = $conn->prepare("SELECT id_galery FROM galery WHERE id_galery = ?");
    $stmt_check->bind_param("i", $id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows == 0) {
        throw new Exception("Data galeri tidak ditemukan!");
    }
    $stmt_check->close();

    // Ambil semua gambar untuk dihapus
    $stmt_images = $conn->prepare("SELECT gambar FROM detail_galery WHERE id_galery = ?");
    $stmt_images->bind_param("i", $id);
    $stmt_images->execute();
    $result_images = $stmt_images->get_result();

    $imagePaths = [];
    while ($row = $result_images->fetch_assoc()) {
        $imagePaths[] = $row['gambar'];
    }
    $stmt_images->close();

    // Hapus detail gambar dari database
    // (Sebenarnya tidak perlu jika 'ON DELETE CASCADE' sudah aktif di DB kamu, tapi ini lebih aman)
    $stmt_delete_detail = $conn->prepare("DELETE FROM detail_galery WHERE id_galery = ?");
    $stmt_delete_detail->bind_param("i", $id);
    if (!$stmt_delete_detail->execute()) {
        throw new Exception("Gagal menghapus detail gambar dari database");
    }
    $stmt_delete_detail->close();

    // Hapus data galeri
    $stmt_delete = $conn->prepare("DELETE FROM galery WHERE id_galery = ?");
    $stmt_delete->bind_param("i", $id);
    if (!$stmt_delete->execute()) {
        throw new Exception("Gagal menghapus data galeri");
    }
    $stmt_delete->close();

    // Commit transaksi
    $conn->commit();

    // Hapus file gambar dari server (setelah berhasil hapus dari DB)
    foreach ($imagePaths as $path) {

        // VVVVV--- PERBAIKAN DI SINI ---VVVVV
        // Kita buat path fisik server yang absolut
        // dirname(__DIR__, 3) = /BackOffice
        $fullPath = dirname(__DIR__, 3) . "/" . $path;
        // ^^^^^--- SELESAI PERBAIKAN ---^^^^^

        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
    }

    echo json_encode([
        'success' => true,
        'message' => 'Galeri berhasil dihapus!',
        'deleted_images' => count($imagePaths)
    ]);
} catch (Exception $e) {
    // Rollback jika ada error
    $conn->rollback();

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
