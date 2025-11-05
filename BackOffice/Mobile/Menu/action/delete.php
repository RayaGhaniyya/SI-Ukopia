<?php
// [UBAH] Path koneksi sesuai lokasi (dari folder action)
include("../../../../Koneksi/koneksi.php");
header('Content-Type: application/json');

// Validasi method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method tidak valid']);
    exit;
}

// [UBAH] Validasi input - nama parameter ID
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID Menu tidak valid!']);
    exit;
}

// [UBAH] Konfigurasi upload directory
// Dari action/ ke Uploads/Menu/ = ../Uploads/Menu/
$UPLOAD_DIR = '../Uploads/Menu/';

try {
    // [UBAH] Cek apakah data exists
    $stmt_check = $conn->prepare("SELECT id_menu FROM menu WHERE id_menu = ?");
    $stmt_check->bind_param("i", $id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows == 0) {
        throw new Exception("Data menu tidak ditemukan!");
    }
    $stmt_check->close();

    // [UBAH] Ambil gambar untuk dihapus dari server
    $stmt_image = $conn->prepare("SELECT gambar_url FROM menu WHERE id_menu = ?");
    $stmt_image->bind_param("i", $id);
    $stmt_image->execute();
    $result_image = $stmt_image->get_result();
    
    $imageUrl = null;
    if ($row = $result_image->fetch_assoc()) {
        $imageUrl = $row['gambar_url'];
    }
    $stmt_image->close();

    // [UBAH] Hapus data menu dari database
    $stmt_delete = $conn->prepare("DELETE FROM menu WHERE id_menu = ?");
    $stmt_delete->bind_param("i", $id);

    if (!$stmt_delete->execute()) {
        throw new Exception("Gagal menghapus data menu");
    }
    $stmt_delete->close();

    // Hapus file gambar dari server (setelah berhasil hapus dari DB)
    $imageDeleted = false;
    if ($imageUrl) {
        // Extract filename dari URL
        // Contoh URL: http://localhost/SI-Ukopia/BackOffice/Mobile/Uploads/Menu/menu_123_1234567890.jpg
        // Extract: menu_123_1234567890.jpg
        $fileName = basename($imageUrl);
        $filePath = $UPLOAD_DIR . $fileName;
        
        if (file_exists($filePath)) {
            if (unlink($filePath)) {
                $imageDeleted = true;
            }
        }
    }

    // [UBAH] Success message
    echo json_encode([
        'success' => true,
        'message' => 'Menu berhasil dihapus!',
        'image_deleted' => $imageDeleted
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();