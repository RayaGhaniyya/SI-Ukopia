<?php
// action/delete.php - Hapus Alat dengan Smart Image Deletion
include("../../../../Koneksi/koneksi.php");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method tidak valid']);
    exit;
}

$id = intval($_POST['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID Alat tidak valid!']);
    exit;
}

$UPLOAD_DIR = '../../Uploads/Alat/';

try {
    // Ambil data yang akan dihapus
    $stmt_check = $conn->prepare("SELECT id_alat, gambar FROM alat WHERE id_alat = ?");
    $stmt_check->bind_param("i", $id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows == 0) {
        throw new Exception("Data Alat tidak ditemukan!");
    }
    
    $row = $result_check->fetch_assoc();
    $gambarName = $row['gambar'];
    $stmt_check->close();

    // Cek apakah gambar masih digunakan oleh alat lain
    $canDeleteImage = false;
    if (!empty($gambarName)) {
        $stmt_count = $conn->prepare("SELECT COUNT(*) as count FROM alat WHERE gambar = ? AND id_alat != ?");
        $stmt_count->bind_param("si", $gambarName, $id);
        $stmt_count->execute();
        $count_result = $stmt_count->get_result()->fetch_assoc();
        $stmt_count->close();
        
        // Jika hanya 1 (data ini sendiri) atau 0, berarti bisa dihapus
        $canDeleteImage = ($count_result['count'] == 0);
    }

    // Hapus data dari database
    $stmt_delete = $conn->prepare("DELETE FROM alat WHERE id_alat = ?");
    $stmt_delete->bind_param("i", $id);

    if (!$stmt_delete->execute()) {
        throw new Exception("Gagal menghapus data Alat");
    }
    
    $stmt_delete->close();
    $conn->close();

    // Hapus file gambar HANYA jika tidak digunakan lagi
    $imageDeleted = false;
    if ($canDeleteImage && !empty($gambarName)) {
        $filePath = $UPLOAD_DIR . $gambarName;
        if (file_exists($filePath)) {
            if (@unlink($filePath)) {
                $imageDeleted = true;
            }
        }
    }

    echo json_encode([
        'success' => true,
        'message' => 'Alat berhasil dihapus!',
        'image_deleted' => $imageDeleted,
        'image_shared' => !$canDeleteImage
    ]);

} catch (Exception $e) {
    if (isset($conn)) $conn->close();
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>